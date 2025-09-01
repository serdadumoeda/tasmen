<?php

namespace App\Providers;

use App\Services\Tte\TteManager;
use Illuminate\Support\ServiceProvider;

class TteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TteManager::class, function ($app) {
            return new TteManager($app);
        });

        // Optional: you could also bind the interface to the manager
        // $this->app->bind(\App\Services\Tte\TteProvider::class, TteManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
