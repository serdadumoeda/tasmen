<?php

namespace App\Services;

use App\Models\User;
use App\Models\Jabatan;
use App\Models\Delegasi;
use Carbon\Carbon;

class ApprovalHierarchyService
{
    /**
     * Mencari approver yang valid untuk seorang user pada saat ini.
     * Akan mencari Plt/Plh terlebih dahulu, jika tidak ada, akan mengembalikan atasan definitif.
     */
    public function findCurrentApproverFor(User $user): ?User
    {
        $jabatanBawahan = $user->jabatan;

        if (!$jabatanBawahan || !$jabatanBawahan->parent) {
            return null; // Tidak punya atasan
        }

        $jabatanAtasan = $jabatanBawahan->parent;

        // 1. Cek Delegasi (Plt./Plh.) yang aktif untuk jabatan atasan
        $delegasiAktif = Delegasi::where('jabatan_id', $jabatanAtasan->id)
            ->where('tanggal_mulai', '<=', Carbon::today())
            ->where('tanggal_selesai', '>=', Carbon::today())
            ->first();

        if ($delegasiAktif) {
            // Jika ada Plt/Plh yang aktif, kembalikan user tersebut
            return $delegasiAktif->user;
        }

        // 2. Jika tidak ada delegasi, kembalikan pejabat definitif
        return $jabatanAtasan->user;
    }
}
