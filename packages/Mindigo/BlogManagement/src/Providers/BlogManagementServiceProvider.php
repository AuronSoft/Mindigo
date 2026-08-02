<?php

namespace Mindigo\BlogManagement\Providers;

use App\Console\Commands\Core\FetchNewsCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BlogManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Routes
        Route::middleware('web')->group(__DIR__.'/../Routes/web.php');

        // Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'blog');

        // Lang
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'blog');

        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                FetchNewsCommand::class,
            ]);
        }
    }
}
