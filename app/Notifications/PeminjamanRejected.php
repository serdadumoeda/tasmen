<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\PeminjamanRequest;

// PASTIKAN NAMA KELAS SESUAI DENGAN NAMA FILE
class PeminjamanRejected extends Notification
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
        $requestedUserName = $this->peminjamanRequest->requestedUser?->name ?? '[Anggota Dihapus]';
        $projectName = $this->peminjamanRequest->project?->name ?? '[Proyek Dihapus]';

        return [
            'peminjaman_request_id' => $this->peminjamanRequest->id,
            'message' => "Permintaan Anda untuk meminjam {$requestedUserName} untuk proyek '{$projectName}' ditolak.",
            'url' => route('peminjaman-requests.my-requests'),
        ];
    }
}