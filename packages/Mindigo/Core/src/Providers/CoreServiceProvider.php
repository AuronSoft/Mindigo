<?php

namespace Mindigo\Core\Providers;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'core');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'core');

        // Thêm dòng này
        $this->callAfterResolving(\Illuminate\View\Compilers\BladeCompiler::class, function ($blade) {
            $blade->anonymousComponentNamespace('core::components', 'core');
        });
    }
}