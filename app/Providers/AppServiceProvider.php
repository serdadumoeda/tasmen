<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use App\Models\Task;
use App\Models\SpecialAssignment;
use App\Policies\UserPolicy;
use App\Policies\TaskPolicy;
use App\Policies\SpecialAssignmentPolicy;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        'App\Models\Project' => 'App\Policies\ProjectPolicy',
        User::class => UserPolicy::class,
        Task::class => TaskPolicy::class,
        SpecialAssignment::class => SpecialAssignmentPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // MODIFIKASI: Hanya superadmin yang mendapatkan hak akses penuh secara global.
        // Hak akses untuk Eselon I dan II akan diatur secara spesifik oleh masing-masing Policy.
        Gate::before(function ($user, $ability) {
            if ($user->role === 'superadmin') {
                return true;
            }
        });
    }
}