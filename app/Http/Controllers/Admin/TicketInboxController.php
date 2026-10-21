<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Notifications\TicketReplied;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketInboxController extends Controller
{
    public function index(): View
    {
        $tickets = Ticket::with(['serviceRequest.user', 'latestMessage'])
            ->latest('updated_at')
            ->paginate(15);

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket): View
    {
        return view('admin.tickets.show', [
            'ticket' => $ticket->load('serviceRequest.user'),
            'messages' => $ticket->messages()
                ->with(['user.citizenProfile', 'user.employeeProfile'])
                ->oldest()
                ->get(),
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        abort_unless($ticket->status === Ticket::OPEN, 403, 'This ticket is closed.');

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $ticket->user->notify(new TicketReplied($ticket));

        return redirect()->route('admin.tickets.show', $ticket);
    }
}
