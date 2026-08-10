<?php

namespace Mindigo\TeacherCalendar\Providers;

use Illuminate\Support\ServiceProvider;

class TeacherCalendarServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teacher-calendar');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teacher-calendar');
    }
}
