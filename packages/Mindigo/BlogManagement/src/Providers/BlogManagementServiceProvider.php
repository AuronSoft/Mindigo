<?php

namespace Mindigo\BlogManagement\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class BlogManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Routes
        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');

        // Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'blog');

        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\Core\FetchNewsCommand::class,
            ]);
        }
    }
}