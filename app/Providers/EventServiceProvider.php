<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Jabatan;
use App\Models\SubTask;
use App\Models\Unit;
use App\Models\Setting;
use App\Observers\JabatanObserver;
use App\Observers\SubTaskObserver;
use App\Observers\UnitObserver;
use App\Observers\SettingObserver;

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
        Jabatan::observe(JabatanObserver::class);
        SubTask::observe(SubTaskObserver::class);
        Unit::observe(UnitObserver::class);
        Setting::observe(SettingObserver::class);
    }
}
