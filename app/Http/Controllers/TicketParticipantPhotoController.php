<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketParticipantPhotoController extends Controller
{
    public function show(Request $request, Ticket $ticket, User $participant): BinaryFileResponse
    {
        $viewer = $request->user();

        if ($viewer->isCitizen()) {
            abort_unless($ticket->user_id === $viewer->id, 404);
        }

        abort_unless(
            $ticket->user_id === $participant->id
                || $ticket->messages()->where('user_id', $participant->id)->exists(),
            404
        );

        $participant->loadMissing(['citizenProfile', 'employeeProfile']);
        $profile = $participant->isCitizen()
            ? $participant->citizenProfile
            : $participant->employeeProfile;
        $path = $profile?->photo_path;
        $disk = Storage::disk('local');

        abort_unless($path && $disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Cache-Control' => 'private, max-age=60',
        ]);
    }
}
