<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        'App\Models\Project' => 'App\Policies\ProjectPolicy',
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            // Eselon I dan II sekarang memiliki hak akses penuh seperti manager sebelumnya
            if ($user->role === 'superadmin' || in_array($user->role, ['Eselon I', 'Eselon II'])) {
                return true;
            }
        });
    }
}