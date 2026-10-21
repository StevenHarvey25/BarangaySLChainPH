<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\IdentityVerification;
use App\Models\User;
use App\Notifications\CitizenSubmissionReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class IdentityVerificationController extends Controller
{
    // How many rejected applications a citizen may have before being blocked
    private const MAX_REJECTED_TRIES = 3;

    // Accepted government IDs
    private array $documentTypes = [
        'Philippine National ID',
        "Driver's License",
        'Passport',
        'UMID',
        'Postal ID',
        "Voter's ID",
        'PRC ID',
        'Barangay ID',
    ];

    // Show Step 1, Step 2, or the current status
    public function show(Request $request)
    {
        $user = $request->user();
        $profile = $user->citizenProfile;

        // Step 2 opens only when Step 1 is complete
        $step = ($request->step == 2 && $profile?->hasCompleteDetails()) ? 2 : 1;

        return view('citizen.identity-verification', [
            'profile' => $profile,
            'verification' => $user->latestIdentityVerification,
            'status' => $user->identityStatus(),
            'step' => $step,
            'documentTypes' => $this->documentTypes,
        ]);
    }

    // Step 1: save the details, then go to Step 2
    public function storeDetails(Request $request)
    {
        if ($this->isLocked($request->user())) {
            return redirect()->route('citizen.identity-verification');
        }

        $data = $request->validate([
            'phone' => 'required|max:15|regex:/^[0-9+\-\s()]{7,20}$/',
            'date_of_birth' => 'required|date|before:today|after:1900-01-01',
            'gender' => 'required|in:Male,Female',
            'civil_status' => 'required|in:Single,Married,Widowed,Separated,Divorced',
            'nationality' => 'required|max:100',
            'place_of_birth' => 'required|max:255',
            'religion' => 'nullable|max:100',
            'occupation' => 'nullable|max:255',
            'house_number' => 'nullable|max:50',
            'barangay' => 'required|max:255',
            'city_municipality' => 'required|max:255',
            'province' => 'required|max:255',
            'zip_code' => 'required|max:10',
        ], [
            'phone.regex' => 'Enter a valid phone number.',
        ]);

        // Join the parts into the one-line "address" column
        $data['address'] = implode(', ', array_filter([
            $data['house_number'] ?? null,
            $data['barangay'],
            $data['city_municipality'],
            $data['province'],
            $data['zip_code'],
        ]));

        $request->user()->citizenProfile()->updateOrCreate([], $data);

        return redirect()->route('citizen.identity-verification', ['step' => 2]);
    }

    // Steps 2-4: ID, selfie and consent are sent together
    public function storeDocument(Request $request)
    {
        $user = $request->user();

        if (! $user->citizenProfile?->hasCompleteDetails()) {
            return redirect()->route('citizen.identity-verification')
                ->withErrors(['details' => 'Please complete your details first.']);
        }

        if ($this->isLocked($user)) {
            return redirect()->route('citizen.identity-verification');
        }

        $request->validate([
            'document_type' => 'required|in:'.implode(',', $this->documentTypes),
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'id_number' => 'required|string|max:50|regex:/^[A-Za-z0-9\-\s]+$/',
            'selfie' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'selfie_source' => 'required|in:camera,upload',
            'agree_truth' => 'accepted',
            'agree_privacy' => 'accepted',
        ], [
            'document.mimes' => 'The ID must be a JPG, PNG, or PDF file.',
            'document.max' => 'The ID must not be larger than 5 MB.',
            'id_number.regex' => 'The ID number may only contain letters, numbers, and dashes.',
            'selfie.image' => 'The selfie must be a JPG or PNG photo.',
            'selfie.mimes' => 'The selfie must be a JPG or PNG photo.',
            'selfie.max' => 'The selfie must not be larger than 5 MB.',
            'agree_truth.accepted' => 'Please confirm that your information is true.',
            'agree_privacy.accepted' => 'Please agree to the data privacy notice.',
        ]);

        // Too many rejected tries
        $rejected = $user->identityVerifications()
            ->where('status', IdentityVerification::REJECTED)
            ->count();

        if ($rejected >= self::MAX_REJECTED_TRIES) {
            return back()->withErrors(['limit' => 'Too many rejected tries. Please visit the barangay office.']);
        }

        // Same ID already used by another account?
        $hash = $this->idNumberHash($request->document_type, $request->id_number);

        if ($this->idAlreadyUsed($hash, $user->id)) {
            return back()->withErrors(['id_number' => 'This ID is already used by another account.']);
        }

        // Save both files in private storage (no public link)
        $file = $request->file('document');

        $user->identityVerifications()->create([
            'status' => IdentityVerification::PENDING,
            'document_type' => $request->document_type,
            'document_path' => $file->store('identity-documents/'.$user->id, 'local'),
            'document_original_name' => $file->getClientOriginalName(),
            'id_number_hash' => $hash,
            'selfie_path' => $request->file('selfie')->store('identity-selfies/'.$user->id, 'local'),
            'selfie_source' => $request->selfie_source,
            'consented_at' => now(),
            'submitted_at' => now(),
        ]);

        try {
            Notification::send(
                User::query()->where('role', 'admin')->where('is_active', true)->get(),
                new CitizenSubmissionReceived(
                    'New identity verification',
                    $user->name.' submitted an identity verification application.',
                    route('admin.identity-verification.index'),
                ),
            );
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()->route('citizen.identity-verification')
            ->with('success', 'Your application was submitted. Please wait for review.');
    }

    // Step 2: check if this ID number is already used (called by the page before Next)
    public function checkIdNumber(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:'.implode(',', $this->documentTypes),
            'id_number' => 'required|string|max:50|regex:/^[A-Za-z0-9\-\s]+$/',
        ], [
            'id_number.regex' => 'The ID number may only contain letters, numbers, and dashes.',
        ]);

        $hash = $this->idNumberHash($request->document_type, $request->id_number);

        return response()->json([
            'used' => $this->idAlreadyUsed($hash, $request->user()->id),
        ]);
    }

    // Only the owner can open their uploaded ID
    public function document(Request $request, IdentityVerification $verification)
    {
        abort_unless(
            $verification->user_id === $request->user()->id && is_file($verification->filePath()),
            404
        );

        return response()->file($verification->filePath());
    }

    // Pending or verified applications cannot be changed or resubmitted
    private function isLocked($user): bool
    {
        return in_array($user->identityStatus(), ['pending', 'verified']);
    }

    // Hash of the ID number, so the real number is never saved.
    // Dashes, spaces and letter case are ignored: "1234-5678" = "12345678".
    private function idNumberHash(string $type, string $number): string
    {
        $clean = preg_replace('/[^a-z0-9]/', '', strtolower($number));

        return hash_hmac('sha256', $type.'|'.$clean, config('app.key'));
    }

    // Is this ID already used by another pending or verified account?
    private function idAlreadyUsed(string $hash, int $userId): bool
    {
        return IdentityVerification::where('id_number_hash', $hash)
            ->where('user_id', '!=', $userId)
            ->whereIn('status', [IdentityVerification::PENDING, IdentityVerification::VERIFIED])
            ->exists();
    }
}
