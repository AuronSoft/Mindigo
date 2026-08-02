<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\ConvertEmptyStringsToNull;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrimStrings;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Mindigo\Auth\Http\Middleware\CheckEmployeeActive;
use Mindigo\Auth\Http\Middleware\CheckRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Chạy SetLocale cho toàn bộ web routes
        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            // Middleware từ Package
            'employee.active' => CheckEmployeeActive::class,
            'role' => CheckRole::class,

            // Middleware của App
            'admin' => AdminMiddleware::class,
            'guest' => RedirectIfAuthenticated::class,
            'permission' => CheckPermission::class,
            'locale' => SetLocale::class,
            'maintenance' => CheckMaintenanceMode::class,

            //  Laravel Default Middleware
            'auth' => Authenticate::class,
            'verified' => EnsureEmailIsVerified::class,
            'throttle' => ThrottleRequests::class,
            'signed' => ValidateSignature::class,
            'cache.headers' => SetCacheHeaders::class,
        ]);

        /**
         * PRIORITY MIDDLEWARE
         */
        $middleware->priority([
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            StartSession::class,
            SetLocale::class,
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
