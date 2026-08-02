<?php

namespace Mindigo\StudentSchedule\Providers;

use Illuminate\Support\ServiceProvider;

class StudentScheduleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'student-schedule');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'student-schedule');
    }
}
