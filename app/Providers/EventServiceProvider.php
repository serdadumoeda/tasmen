<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SubTask;
use App\Observers\SubTaskObserver;
use App\Models\Unit;
use App\Observers\UnitObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        SubTask::observe(SubTaskObserver::class);
        Unit::observe(UnitObserver::class);
    }
}
