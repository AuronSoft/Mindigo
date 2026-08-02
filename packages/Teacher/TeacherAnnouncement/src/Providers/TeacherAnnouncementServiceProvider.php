<?php

namespace Mindigo\TeacherAnnouncement\Providers;

use Illuminate\Support\ServiceProvider;

class TeacherAnnouncementServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teacher-announcement');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teacher-announcement');
    }
}
