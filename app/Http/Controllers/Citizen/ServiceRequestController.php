<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\CitizenSubmissionReceived;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    private const TYPES = [
        'indigency' => [
            'service' => ServiceRequest::CERTIFICATE_OF_INDIGENCY,
            'title' => 'Certificate of Indigency',
            'route' => 'citizen.certificate-of-indigency',
            'heading' => 'Certificate of indigency',
            'description' => 'Ask the barangay about eligibility and requirements for this certificate.',
            'flag' => 'isIndigency',
        ],
        'clearance' => [
            'service' => ServiceRequest::BARANGAY_CLEARANCE,
            'title' => 'Barangay Clearance',
            'route' => 'citizen.barangay-clearance',
            'heading' => 'Barangay clearance',
            'description' => 'Request a clearance for employment, business, or other official purposes.',
            'flag' => 'isClearance',
        ],
        'residency' => [
            'service' => ServiceRequest::CERTIFICATE_OF_RESIDENCY,
            'title' => 'Certificate of Residency',
            'route' => 'citizen.certificate-of-residency',
            'heading' => 'Certificate of residency',
            'description' => 'Request proof of residency within the barangay.',
            'flag' => 'isResidency',
        ],
    ];

    // Form pages

    public function indigency(Request $request)
    {
        return $this->form($request, 'indigency');
    }

    public function clearance(Request $request)
    {
        return $this->form($request, 'clearance');
    }

    public function residency(Request $request)
    {
        return $this->form($request, 'residency');
    }

    // Preview

    public function previewIndigency(Request $request)
    {
        return $this->previewRequest($request, 'indigency');
    }

    public function previewClearance(Request $request)
    {
        return $this->previewRequest($request, 'clearance');
    }

    public function previewResidency(Request $request)
    {
        return $this->previewRequest($request, 'residency');
    }

    // Modify

    public function modifyIndigency(Request $request)
    {
        return $this->modifyRequest($request, 'indigency');
    }

    public function modifyClearance(Request $request)
    {
        return $this->modifyRequest($request, 'clearance');
    }

    public function modifyResidency(Request $request)
    {
        return $this->modifyRequest($request, 'residency');
    }

    // Proceed

    public function proceedIndigency(Request $request)
    {
        return $this->proceedRequest($request, 'indigency');
    }

    public function proceedClearance(Request $request)
    {
        return $this->proceedRequest($request, 'clearance');
    }

    public function proceedResidency(Request $request)
    {
        return $this->proceedRequest($request, 'residency');
    }

    // Lists and details

    public function applications(Request $request): View
    {
        $applications = $this->ownedRequests($request)
            ->with('ticket')
            ->where('status', '!=', ServiceRequest::APPROVED)
            ->paginate(10);

        return view('citizen.applications', compact('applications'));
    }

    public function documents(Request $request): View
    {
        $documents = $this->ownedRequests($request)
            ->with('ticket')
            ->where('status', ServiceRequest::APPROVED)
            ->paginate(10);

        return view('citizen.documents', compact('documents'));
    }

    public function showApplication(Request $request, ServiceRequest $serviceRequest): View
    {
        $this->authorizeOwner($request, $serviceRequest);

        $ticket = $serviceRequest->ticket;

        return view('citizen.application-review', [
            'application' => $serviceRequest,
            'documentTitle' => $this->documentTitle($serviceRequest),
            'ticket' => $ticket,
            'recentMessages' => $ticket?->messages()->latest()->take(3)->get()->reverse(),
        ]);
    }

    public function printApplication(Request $request, ServiceRequest $serviceRequest): View
    {
        $this->authorizeOwner($request, $serviceRequest);

        $serviceRequest = DB::transaction(function () use ($serviceRequest) {
            $locked = $this->lock($serviceRequest);

            abort_unless($locked->status === ServiceRequest::APPROVED, 404);
            abort_unless($locked->canPrint(), 403, 'This permit has reached its print limit.');

            $locked->update([
                'permit_number' => $locked->permit_number ?? ServiceRequest::generatePermitNumber(),
                'print_count' => $locked->print_count + 1,
            ]);

            return $locked->fresh();
        });

        $url = URL::signedRoute('permits.verify', ['serviceRequest' => $serviceRequest]);

        return view('citizen.application-print', [
            'application' => $serviceRequest,
            'documentTitle' => $this->documentTitle($serviceRequest),
            'qrCode' => $this->qrCode($url),
            'verificationUrl' => $url,
        ]);
    }

    public function cancelApplication(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->authorizeOwner($request, $serviceRequest);

        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $cancelled = DB::transaction(function () use ($serviceRequest, $data) {
            $locked = $this->lock($serviceRequest);

            $cancelled = $locked->isCancellable() && $locked->update([
                'status' => ServiceRequest::CANCELLED,
                'cancellation_reason' => $data['cancellation_reason'],
                'cancelled_at' => now(),
                'permit_number' => null,
                'rejection_reason' => null,
            ]);

            if ($cancelled) {
                $locked->ticket?->postSystemMessage('This request was canceled.', close: true);
            }

            return $cancelled;
        });

        $redirect = redirect()->route('citizen.applications.show', $serviceRequest);

        return $cancelled
            ? $redirect->with('success', 'Your application was canceled successfully.')
            : $redirect->withErrors([
                'cancellation_reason' => 'This application can only be canceled while pending and within two hours of submission.',
            ]);
    }

    // Helpers

    private function form(Request $request, string $type): View|RedirectResponse
    {
        if ($this->pendingQuery($request, $type)->exists()) {
            return $this->pendingBlocked();
        }

        $info = self::TYPES[$type];

        return view('citizen.service-request', [
            'title' => $info['heading'],
            'description' => $info['description'],
            'profile' => $request->user()->citizenProfile,
            $info['flag'] => true,
        ]);
    }

    private function previewRequest(Request $request, string $type): View
    {
        $data = $request->validate($this->requestRules());
        $request->session()->put("{$type}_request", $data);

        return view('citizen.service-request-preview', [
            'requestData' => $data,
            'documentTitle' => self::TYPES[$type]['title'],
            'type' => $type,
        ]);
    }

    private function modifyRequest(Request $request, string $type): RedirectResponse
    {
        $data = $request->session()->get("{$type}_request");

        return is_array($data)
            ? redirect()->route(self::TYPES[$type]['route'])->withInput($data)
            : $this->previewExpired($type);
    }

    private function proceedRequest(Request $request, string $type): RedirectResponse
    {
        $data = $request->session()->get("{$type}_request");

        if (! is_array($data)) {
            return $this->previewExpired($type);
        }

        $validated = validator($data, $this->requestRules())->validate();

        $serviceRequest = DB::transaction(function () use ($request, $type, $validated) {
            if ($this->pendingQuery($request, $type)->lockForUpdate()->exists()) {
                return null;
            }

            $serviceRequest = ServiceRequest::create([
                'user_id' => $request->user()->id,
                'reference_number' => 'BRGY-'.now()->format('YmdHis').'-'.str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                'service_type' => self::TYPES[$type]['service'],
                'applicant_name' => $validated['name'],
                'address' => $validated['address'],
                'age' => $validated['age'],
                'purpose' => $validated['purpose'],
                'status' => ServiceRequest::PENDING,
            ]);

            // Open a ticket with an automatic first message
            $ticket = $serviceRequest->ticket()->create(['user_id' => $request->user()->id]);

            $ticket->messages()->create([
                'body' => "Your request {$serviceRequest->reference_number} was submitted. The barangay will post updates here.",
                'is_system' => true,
            ]);

            return $serviceRequest;
        });

        $request->session()->forget("{$type}_request");

        if ($serviceRequest) {
            try {
                Notification::send(
                    User::query()->where('role', 'admin')->where('is_active', true)->get(),
                    new CitizenSubmissionReceived(
                        'New permit request',
                        $request->user()->name.' submitted '.$serviceRequest->reference_number.' for review.',
                        route('admin.permits.show', $serviceRequest),
                    ),
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $serviceRequest
            ? redirect()->route('citizen.applications')->with('success', "Your certificate request {$serviceRequest->reference_number} was submitted for barangay processing.")
            : $this->pendingBlocked();
    }

    private function ownedRequests(Request $request)
    {
        return ServiceRequest::whereBelongsTo($request->user())->latest();
    }

    private function pendingQuery(Request $request, string $type)
    {
        return ServiceRequest::whereBelongsTo($request->user())
            ->where('service_type', self::TYPES[$type]['service'])
            ->where('status', ServiceRequest::PENDING);
    }

    private function authorizeOwner(Request $request, ServiceRequest $serviceRequest): void
    {
        abort_unless($serviceRequest->user_id === $request->user()->id, 404);
    }

    private function lock(ServiceRequest $serviceRequest): ServiceRequest
    {
        return ServiceRequest::whereKey($serviceRequest->id)->lockForUpdate()->firstOrFail();
    }

    private function qrCode(string $data): string
    {
        return (new Builder(writer: new SvgWriter, data: $data, size: 120, margin: 4))
            ->build()
            ->getDataUri();
    }

    private function documentTitle(ServiceRequest $serviceRequest): string
    {
        return collect(self::TYPES)->firstWhere('service', $serviceRequest->service_type)['title']
            ?? self::TYPES['indigency']['title'];
    }

    private function requestRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'age' => ['required', 'integer', 'min:0', 'max:150'],
            'purpose' => ['required', 'string', 'max:500'],
        ];
    }

    private function previewExpired(string $type): RedirectResponse
    {
        return redirect()
            ->route(self::TYPES[$type]['route'])
            ->withErrors(['purpose' => 'Your preview expired. Please fill out the form again.']);
    }

    private function pendingBlocked(): RedirectResponse
    {
        return redirect()
            ->route('citizen.applications')
            ->with('error', 'You already have a pending request for this service.');
    }
}
