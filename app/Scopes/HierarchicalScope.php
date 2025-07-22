<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class HierarchicalScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();

        // Jika tidak ada pengguna yang login atau superadmin, jangan lakukan apa-apa.
        if (!$user || $user->role === User::ROLE_SUPERADMIN) {
            return;
        }

        // Mulai satu blok query terpadu untuk mencakup semua kondisi.
        $builder->where(function ($query) use ($user) {
            
            // Pengguna akan melihat proyek jika ia terlibat langsung sebagai:
            // 1. Pemilik (owner)
            // 2. Ketua Tim (leader)
            // 3. Anggota Tim (member)
            $query->where('owner_id', $user->id)
                  ->orWhere('leader_id', $user->id)
                  ->orWhereHas('members', function ($subQuery) use ($user) {
                      $subQuery->where('users.id', $user->id);
                  });

            // Jika pengguna adalah manajer, ia JUGA dapat melihat
            // semua proyek yang dimiliki oleh tim bawahannya berdasarkan unit.
            if ($user->isManager() && $user->unit) {
                $subordinateUnitIds = Cache::remember('subordinate_unit_ids_for_user_'.$user->id, 3600, function () use ($user) {
                    return $user->unit->getAllSubordinateUnitIds();
                });

                if (!empty($subordinateUnitIds)) {
                    // Dapatkan semua ID pengguna di unit bawahan
                    $subordinateUserIds = User::whereIn('unit_id', $subordinateUnitIds)->pluck('id');

                    if ($subordinateUserIds->isNotEmpty()) {
                        $query->orWhereIn('owner_id', $subordinateUserIds)
                              ->orWhereIn('leader_id', $subordinateUserIds);
                    }
                }
            }
        });
    }
}