<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\TrustProxies::class);

        // Daftarkan middleware di sini
        $middleware->web(append: [
            \App\Http\Middleware\MarkNotificationAsRead::class,
            \App\Http\Middleware\CheckProfileIsComplete::class,
            \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->alias([
            'superadmin' => \App\Http\Middleware\CheckSuperadmin::class,
            'can.manage.leave.settings' => \App\Http\Middleware\CheckCanManageLeaveSettings::class,
            'auth.apikey' => \App\Http\Middleware\AuthenticateApiClient::class,
            'log.api' => \App\Http\Middleware\LogApiActivity::class,
            'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (PostTooLargeException $e, \Illuminate\Http\Request $request) {
            return back()->withErrors(['file' => 'Ukuran file yang diunggah terlalu besar. Silakan coba lagi dengan file yang lebih kecil.']);
        });
    })->create();