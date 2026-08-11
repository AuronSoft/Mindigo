<?php

namespace Mindigo\TeacherLiveSession\Providers;

use Illuminate\Support\ServiceProvider;
use Mindigo\TeacherLiveSession\Providers\Meetings\MindigoNativeProvider;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;

class TeacherLiveSessionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LiveMeetingProviderRegistry::class, function ($app): LiveMeetingProviderRegistry {
            $registry = new LiveMeetingProviderRegistry;
            $registry->register($app->make(MindigoNativeProvider::class));

            return $registry;
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teacher-live-session');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teacher-live-session');
    }
}
