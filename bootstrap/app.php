<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {

            // Administrator routes
            Route::middleware(['web', 'auth'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));


            // Kepegawaian routes
            Route::middleware(['auth', 'web'])
                ->prefix('kepegawaian')
                ->name('kepegawaian.')
                ->group(base_path('routes/kepegawaian.php'));


            // Umum routes
            Route::middleware(['auth', 'web'])
                ->prefix('umum')
                ->name('umum.')
                ->group(base_path('routes/umum.php'));


            // Keuangan Route
            Route::middleware(['auth', 'web'])
                ->prefix('keuangan')
                ->name('keuangan.')
                ->group(base_path('routes/keuangan.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
