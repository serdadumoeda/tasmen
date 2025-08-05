<?php

namespace App\Policies;

use App\Models\PeminjamanRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PeminjamanRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Semua user yang login bisa melihat halaman permintaan mereka.
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PeminjamanRequest $peminjamanRequest): bool
    {
        // User bisa melihat detail jika dia adalah peminta atau approver.
        return $user->id === $peminjamanRequest->requester_id || $user->id === $peminjamanRequest->approver_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Asumsi: Hanya manajer proyek (yang bisa membuat proyek) yang bisa meminta pinjaman anggota.
        return $user->canCreateProjects();
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, PeminjamanRequest $peminjamanRequest): bool
    {
        // Hanya approver yang ditunjuk yang bisa menyetujui.
        return $user->id === $peminjamanRequest->approver_id && $peminjamanRequest->status === 'pending';
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, PeminjamanRequest $peminjamanRequest): bool
    {
        // Hanya approver yang ditunjuk yang bisa menolak.
        return $user->id === $peminjamanRequest->approver_id && $peminjamanRequest->status === 'pending';
    }


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PeminjamanRequest $peminjamanRequest): bool
    {
        // Hanya peminta atau approver yang bisa menghapus riwayat.
        return $user->id === $peminjamanRequest->requester_id || $user->id === $peminjamanRequest->approver_id;
    }
}
