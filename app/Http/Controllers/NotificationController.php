<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getUnread()
    {
        return response()->json([
            'unread' => Auth::user()->unreadNotifications,
            'count' => Auth::user()->unreadNotifications->count()
        ]);
    }

    public function markAsRead(Request $request)
    {
        $notificationId = $request->input('id');
        if ($notificationId) {
            $notification = Auth::user()->notifications()->find($notificationId);
            if ($notification) {
                $notification->markAsRead();
            }
        }
        return response()->json(['success' => true]);
    }

    public function readAndRedirect(\Illuminate\Notifications\DatabaseNotification $notification)
    {
        // Ensure the user is authorized to read this notification
        if (auth()->user()->id !== $notification->notifiable_id) {
            abort(403);
        }

        $notification->markAsRead();

        if (isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }

        // Fallback if there's no URL
        return redirect()->route('dashboard');
    }
}