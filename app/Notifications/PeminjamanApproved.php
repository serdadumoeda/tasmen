<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\PeminjamanRequest;

class PeminjamanApproved extends Notification
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
        // --- PERBAIKAN DI SINI ---
        // Gunakan operator null-safe (?->) untuk mencegah error jika relasi null
        $requestedUserName = $this->peminjamanRequest->requestedUser?->name ?? '[Anggota Dihapus]';
        $projectName = $this->peminjamanRequest->project?->name ?? '[Proyek Dihapus]';
        // --- AKHIR PERBAIKAN ---

        return [
            'peminjaman_request_id' => $this->peminjamanRequest->id,
            'message' => "Permintaan Anda untuk meminjam {$requestedUserName} untuk proyek '{$projectName}' telah disetujui.",
            'url' => route('projects.show', $this->peminjamanRequest->project_id),
        ];
    }
}