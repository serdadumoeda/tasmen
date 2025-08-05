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
}