<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class MarkNotificationAsRead
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('notification_id')) {
            $notification = $request->user()->notifications()->where('id', $request->notification_id)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }
        return $next($request);
    }
}