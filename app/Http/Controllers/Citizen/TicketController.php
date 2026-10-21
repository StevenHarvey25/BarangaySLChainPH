<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function show(Request $request, ServiceRequest $serviceRequest): View
    {
        $ticket = $this->ownedTicket($request, $serviceRequest);

        return view('citizen.ticket', [
            'application' => $serviceRequest,
            'ticket' => $ticket,
            'messages' => $ticket->messages()
                ->with(['user.citizenProfile', 'user.employeeProfile'])
                ->oldest()
                ->get(),
        ]);
    }

    public function reply(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $ticket = $this->ownedTicket($request, $serviceRequest);

        abort_unless($ticket->status === Ticket::OPEN, 403, 'This ticket is closed.');

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return redirect()->route('citizen.tickets.show', $serviceRequest);
    }

    // Only the owner can open the ticket (same idea as authorizeOwner)
    private function ownedTicket(Request $request, ServiceRequest $serviceRequest): Ticket
    {
        $ticket = $serviceRequest->ticket()->firstOrFail();

        abort_unless($ticket->user_id === $request->user()->id, 404);

        return $ticket;
    }
}
