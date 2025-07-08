<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\ProjectListComposer;


use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use App\Models\SpecialAssignment;
use App\Policies\ProjectPolicy;
use App\Policies\UserPolicy;
use App\Policies\TaskPolicy;
use App\Policies\SpecialAssignmentPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        User::class => UserPolicy::class,
        Task::class => TaskPolicy::class,
        SpecialAssignment::class => SpecialAssignmentPolicy::class,
    ];

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
            if ($user->role === 'Superadmin') {
                return true;
            }
        });

        // Daftarkan View Composer
        View::composer('layouts.navigation', ProjectListComposer::class);
    }
}