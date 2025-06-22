<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class HierarchicalScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // MODIFIKASI: Hanya superadmin yang bisa melihat semua data tanpa filter.
            if ($user->role === 'superadmin') {
                return; // Langsung keluar, jangan terapkan filter.
            }

            // Untuk semua role lain (Eselon I, II, Koordinator, dst.),
            // dapatkan semua ID bawahan mereka.
            $subordinateIds = $user->getAllSubordinateIds();
            $subordinateIds[] = $user->id; // Termasuk diri sendiri

            // Terapkan filter utama:
            // User hanya bisa melihat proyek yang 'owner_id'-nya adalah
            // diri mereka sendiri atau salah satu dari bawahan mereka.
            $builder->whereIn('owner_id', $subordinateIds);
        }
    }
}