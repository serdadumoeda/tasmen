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
        // Hanya terapkan scope jika ada user yang login
        if (Auth::check()) {
            $user = Auth::user();

            // Superadmin dan Eselon I bisa melihat semua data, jadi tidak perlu filter.
            if ($user->role === 'superadmin' || $user->role === 'Eselon I') {
                return; // Langsung keluar, jangan terapkan filter apapun.
            }

            // Untuk role lain, dapatkan semua ID bawahan mereka.
            $subordinateIds = $user->getAllSubordinateIds();
            // Tambahkan ID user itu sendiri ke dalam daftar agar bisa melihat proyek milik sendiri
            $subordinateIds[] = $user->id;

            // Terapkan filter utama:
            // User hanya bisa melihat proyek yang 'owner_id'-nya adalah
            // diri mereka sendiri atau salah satu dari bawahan mereka.
            $builder->whereIn('owner_id', $subordinateIds);
        }
    }
}