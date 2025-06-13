<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate; // Pastikan Gate di-import
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */


    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user->role === 'kepala_pusdatik') {
                return true;
            }
        });
    }

    protected $policies = [
        'App\Models\Project' => 'App\Policies\ProjectPolicy', 
    ];
}
