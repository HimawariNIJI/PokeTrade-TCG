<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->paginate(20);

        // Visiting the index marks the unread ones as read.
        $user->unreadNotifications->markAsRead();

        return view('pages.notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return back()->with('status', 'Notification dismissed.');
    }
}
