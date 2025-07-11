<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class HierarchicalScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();

        // Jika tidak ada pengguna yang login, jangan lakukan apa-apa.
        if (!$user) {
            return;
        }

        // Mulai satu blok query terpadu untuk mencakup semua kondisi.
        $builder->where(function ($query) use ($user) {
            
            // ATURAN DASAR (berlaku untuk semua peran):
            // Pengguna akan melihat proyek jika ia terlibat langsung sebagai:
            // 1. Pemilik (owner)
            // 2. Ketua Tim (leader)
            // 3. Anggota Tim (member)
            $query->where('owner_id', $user->id)
                  ->orWhere('leader_id', $user->id)
                  ->orWhereHas('members', function ($subQuery) use ($user) {
                      $subQuery->where('users.id', $user->id);
                  });

            // ATURAN TAMBAHAN (hanya untuk peran manajerial):
            // Cek apakah pengguna memiliki peran level pimpinan.
            // Kita gunakan string langsung untuk menghindari potensi error 'Undefined Constant'.
            $isManagerLevel = $user->canManageUsers() || in_array($user->role, [
                'koordinator',
                'sub_koordinator'
            ]);

            // Jika pengguna adalah level pimpinan, ia JUGA dapat melihat
            // semua proyek yang dimiliki (owner_id) oleh tim bawahannya.
            if ($isManagerLevel) {
                $subordinateIds = $user->getAllSubordinateIds();

                if (!empty($subordinateIds)) {
                    $query->orWhereIn('owner_id', $subordinateIds);
                }
            }
        });
    }
}