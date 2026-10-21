<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Full list page
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(10);

        return view('citizen.notifications', compact('notifications'));
    }

    // Mark one as read.
    // - Dismiss button in the bell (fetch) gets JSON back.
    // - Clicking an item on the notifications page goes to its link.
    public function read(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect($notification->data['url'] ?? route('citizen.notifications'));
    }

    // Mark all as read
    public function readAll(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }
}
