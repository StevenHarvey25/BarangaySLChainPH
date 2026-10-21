<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\CitizenProfile;
use App\Services\ProfilePhotoStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $verified = $user->isIdentityVerified();

        return view('citizen.profile', [
            'user' => $user,
            'profile' => $user->citizenProfile,
            'status' => $user->identityStatus(),
            'verified' => $verified,
            // Stay unlocked when a save failed, so the errors are visible
            'editing' => $verified && session()->hasOldInput(),
        ]);
    }

    public function update(Request $request)
    {
        $profile = $this->verifiedProfile($request);

        $data = $request->validate([
            'phone' => 'required|max:30|regex:/^[0-9+\-\s()]{7,20}$/',
            'civil_status' => 'required|in:Single,Married,Widowed,Separated,Divorced',
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

        $data['address'] = implode(', ', array_filter([
            $data['house_number'] ?? null,
            $data['barangay'],
            $data['city_municipality'],
            $data['province'],
            $data['zip_code'],
        ]));

        // Update via the model so the encrypted casts still apply
        $profile->update($data);

        return redirect()->route('citizen.profile')->with('success', 'Your profile was updated.');
    }

    public function photo(Request $request, ProfilePhotoStorage $photos)
    {
        $profile = $this->verifiedProfile($request);

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'photo.max' => 'The photo must not be larger than 2 MB.',
        ]);

        $newPath = $photos->store($request->file('photo'), 'profile-photos');
        $oldPath = $profile->photo_path;

        $profile->update([
            'photo_path' => $newPath,
        ]);

        $photos->delete($oldPath);

        return redirect()->route('citizen.profile')->with('success', 'Your photo was updated.');
    }

    public function showPhoto(Request $request, ProfilePhotoStorage $photos): Response
    {
        $path = $request->user()->citizenProfile?->photo_path;

        abort_unless($path, 404);

        return $photos->response($path);
    }

    private function verifiedProfile(Request $request): CitizenProfile
    {
        $user = $request->user();

        abort_unless($user->isIdentityVerified() && $user->citizenProfile, 403);

        return $user->citizenProfile;
    }
}
