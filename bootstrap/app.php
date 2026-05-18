<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',   
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Chạy SetLocale cho toàn bộ web routes
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            // Middleware từ Package 
            'employee.active' => \Mindigo\Auth\Http\Middleware\CheckEmployeeActive::class,
            'role'            => \Mindigo\Auth\Http\Middleware\CheckRole::class,

            // Middleware của App 
            'admin'             => \App\Http\Middleware\AdminMiddleware::class,
            'guest'             => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'permission'        => \App\Http\Middleware\CheckPermission::class,
            'locale'            => \App\Http\Middleware\SetLocale::class,
            'maintenance'       => \App\Http\Middleware\CheckMaintenanceMode::class,

            //  Laravel Default Middleware 
            'auth'              => \Illuminate\Auth\Middleware\Authenticate::class,
            'verified'          => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'throttle'          => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'signed'            => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'cache.headers'     => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        ]);

        /**
         * PRIORITY MIDDLEWARE 
         */
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \App\Http\Middleware\ConvertEmptyStringsToNull::class,
            \App\Http\Middleware\SetLocale::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // Xử lý exception trả về JSON cho API
        $exceptions->shouldRenderJsonWhen(function ($request) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->dontReportDuplicates();
        
    })
    ->create();