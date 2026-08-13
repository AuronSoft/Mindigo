<?php

namespace Mindigo\TeacherLiveSession\Providers;

use App\Console\Commands\LiveSession\BackupLiveSessionDataCommand;
use App\Console\Commands\LiveSession\CheckTurnHealthCommand;
use App\Console\Commands\LiveSession\CleanupLiveRealtimeCommand;
use App\Console\Commands\LiveSession\DoctorLiveSessionCommand;
use App\Console\Commands\LiveSession\PruneLiveSessionDataCommand;
use App\Console\Commands\LiveSession\RestoreLiveSessionDataCommand;
use App\Console\Commands\LiveSession\SyncLiveProvidersCommand;
use Illuminate\Support\ServiceProvider;
use Mindigo\TeacherLiveSession\Providers\Meetings\GoogleMeetProvider;
use Mindigo\TeacherLiveSession\Providers\Meetings\MindigoNativeProvider;
use Mindigo\TeacherLiveSession\Providers\Meetings\ZoomProvider;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;
use Mindigo\TeacherLiveSession\Services\LiveSessionConfigurationService;

class TeacherLiveSessionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/live-media.php', 'live-media');
        $this->mergeConfigFrom(__DIR__.'/../config/live-providers.php', 'live-providers');
        $this->app->singleton(LiveMeetingProviderRegistry::class, function ($app): LiveMeetingProviderRegistry {
            $registry = new LiveMeetingProviderRegistry($app->make(LiveSessionConfigurationService::class));
            $registry->register($app->make(MindigoNativeProvider::class));
            $registry->register($app->make(GoogleMeetProvider::class));
            $registry->register($app->make(ZoomProvider::class));

            return $registry;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([SyncLiveProvidersCommand::class, CleanupLiveRealtimeCommand::class, CheckTurnHealthCommand::class, DoctorLiveSessionCommand::class, PruneLiveSessionDataCommand::class, BackupLiveSessionDataCommand::class, RestoreLiveSessionDataCommand::class]);
        }
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teacher-live-session');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teacher-live-session');
    }
}
