<?php

namespace App\Providers;

use App\Models\PeminjamanRequest;
use App\Models\Project;
use App\Models\SpecialAssignment;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use App\Policies\PeminjamanRequestPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\SpecialAssignmentPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UnitPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Unit::class => UnitPolicy::class,
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        User::class => UserPolicy::class,
        PeminjamanRequest::class => PeminjamanRequestPolicy::class,
        SpecialAssignment::class => SpecialAssignmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
