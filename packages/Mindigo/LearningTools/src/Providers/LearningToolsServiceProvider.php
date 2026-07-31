<?php

namespace Mindigo\LearningTools\Providers;

use Illuminate\Support\ServiceProvider;

class LearningToolsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'learning-tools');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'learning-tools');
    }
}
