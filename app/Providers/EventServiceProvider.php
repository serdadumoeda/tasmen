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
use App\Models\User;
use App\Observers\UserObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\SuratPeminjamanDisetujui::class => [
            \App\Listeners\CreatePeminjamanRequest::class,
        ],
    ];

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
        User::observe(UserObserver::class);
    }
}
