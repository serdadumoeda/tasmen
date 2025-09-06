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
use App\Models\LampiranSurat;
use App\Models\Setting;
use App\Policies\LampiranSuratPolicy;
use App\Models\Surat;
use App\Policies\SuratPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UnitPolicy;
use App\Policies\UserPolicy;
use App\Policies\SettingPolicy;
use App\Models\ApprovalWorkflow;
use App\Policies\ApprovalWorkflowPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        LampiranSurat::class => LampiranSuratPolicy::class,
        Surat::class => SuratPolicy::class,
        Setting::class => SettingPolicy::class,
        \App\Models\ApprovalWorkflow::class => \App\Policies\ApprovalWorkflowPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            // First, check if a Superadmin is impersonating another user.
            // If so, they should retain all their original permissions.
            if (session()->has('impersonator_id')) {
                $impersonator = User::find(session('impersonator_id'));
                if ($impersonator && $impersonator->isSuperAdmin()) {
                    return true;
                }
            }

            // If not impersonating, fall back to the normal Superadmin check.
            if ($user->isSuperAdmin()) {
                return true;
            }

            return null; // Defer to the model's policy for other users.
        });
    }
}
