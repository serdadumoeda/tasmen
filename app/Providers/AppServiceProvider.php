<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\ProjectListComposer;
use App\Services\BreadcrumbService;
use App\Http\View\Composers\BreadcrumbComposer;


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
        $this->app->register(\SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class);
        $this->app->singleton(BreadcrumbService::class, function ($app) {
            return new BreadcrumbService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('QrCode', \SimpleSoftwareIO\QrCode\Facades\QrCode::class);

        Gate::before(function ($user, $ability) {
            if ($user->role === 'Superadmin') {
                return true;
            }
        });

        // Daftarkan View Composer
        View::composer('layouts.navigation', ProjectListComposer::class);
        View::composer('components.breadcrumbs', BreadcrumbComposer::class);
    }
}