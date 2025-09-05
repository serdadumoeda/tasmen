<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public function getUnreadCount(): int
    {
        if (Auth::check()) {
            return Auth::user()->unreadNotifications()->count();
        }

        return 0;
    }
}
