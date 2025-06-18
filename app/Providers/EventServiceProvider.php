<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SubTask;        
use App\Observers\SubTaskObserver; 

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
    }
}
