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

        $targetUrl = $notification->data['url']
            ?? $notification->data['link']
            ?? $this->resolveRedirectUrl($notification);

        return $targetUrl
            ? redirect($targetUrl)
            : redirect()->route('dashboard');
    }

    protected function resolveRedirectUrl(\Illuminate\Notifications\DatabaseNotification $notification): ?string
    {
        $data = $notification->data ?? [];
        $type = $notification->type;

        return match ($type) {
            'App\\Notifications\\TaskAssigned' => $this->resolveTaskAssignedUrl($data),
            'App\\Notifications\\TaskRequiresApproval' => isset($data['task_id']) ? route('tasks.edit', $data['task_id']) : null,
            'App\\Notifications\\NewCommentOnTask' => $this->resolveProjectTaskUrl($data),
            'App\\Notifications\\UserMentioned' => $data['link'] ?? null,
            'App\\Notifications\\LeaveRequestSubmitted',
            'App\\Notifications\\LeaveRequestForwarded',
            'App\\Notifications\\LeaveRequestStatusUpdated' => isset($data['leave_request_id']) ? route('leaves.show', $data['leave_request_id']) : null,
            'App\\Notifications\\PeminjamanApproved' => isset($data['project_id']) ? route('projects.show', $data['project_id']) : null,
            'App\\Notifications\\PeminjamanRejected',
            'App\\Notifications\\PeminjamanRequested' => route('peminjaman-requests.my-requests'),
            'App\\Notifications\\SuratDisposisiNotification' => isset($data['surat_id']) ? route('surat.show', $data['surat_id']) : null,
            default => null,
        };
    }

    protected function resolveTaskAssignedUrl(array $data): ?string
    {
        if (!empty($data['project_id'])) {
            return $this->resolveProjectTaskUrl($data);
        }

        return !empty($data['task_id']) ? route('tasks.edit', $data['task_id']) : null;
    }

    protected function resolveProjectTaskUrl(array $data): ?string
    {
        if (empty($data['project_id'])) {
            return null;
        }

        $url = route('projects.show', $data['project_id']);

        if (!empty($data['task_id'])) {
            $url .= '#task-' . $data['task_id'];
        }

        return $url;
    }
}
