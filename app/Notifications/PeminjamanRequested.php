<?php

// PASTIKAN NAMESPACE-NYA BENAR: App\Notifications
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PeminjamanRequest;

class PeminjamanRequested extends Notification
{
    use Queueable;

    public function __construct(public PeminjamanRequest $peminjamanRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        // Menggunakan operator null-safe untuk keamanan
        $requesterName = $this->peminjamanRequest->requester?->name ?? '[Peminta Dihapus]';
        $requestedUserName = $this->peminjamanRequest->requestedUser?->name ?? '[Anggota Dihapus]';
        $projectName = $this->peminjamanRequest->project?->name ?? '[Kegiatan Dihapus]';

        return [
            'peminjaman_request_id' => $this->peminjamanRequest->id,
            'message' => "{$requesterName} meminta izin untuk menugaskan {$requestedUserName} untuk kegiatan '{$projectName}'.",
            'url' => route('peminjaman-requests.my-requests'),
        ];
    }
}