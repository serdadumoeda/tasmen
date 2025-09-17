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

                // Redirect to the URL from the notification data, if it exists
                if (isset($notification->data['url'])) {
                    return redirect($notification->data['url']);
                }
            }
        }
        return $next($request);
    }
}