<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\ProjectListComposer;
use App\Services\BreadcrumbService;
use App\Http\View\Composers\BreadcrumbComposer;
use App\Services\PageTitleService;
use App\Services\NotificationService;
use App\Http\View\Composers\PageTitleComposer;
use Illuminate\Support\Facades\URL;


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
        $this->app->singleton(PageTitleService::class, function ($app) {
            return new PageTitleService();
        });
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

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
        View::composer('layouts.app', PageTitleComposer::class);
    }
}