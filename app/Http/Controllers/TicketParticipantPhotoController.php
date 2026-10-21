<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\ProfilePhotoStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketParticipantPhotoController extends Controller
{
    public function show(
        Request $request,
        Ticket $ticket,
        User $participant,
        ProfilePhotoStorage $photos,
    ): Response {
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

        abort_unless($path, 404);

        return $photos->response($path);
    }
}
