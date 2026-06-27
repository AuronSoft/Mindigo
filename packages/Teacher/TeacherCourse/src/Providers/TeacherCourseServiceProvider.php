<?php

namespace Mindigo\TeacherCourse\Providers;

use Illuminate\Support\ServiceProvider;

class TeacherCourseServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'teacher-course');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'teacher-course');
    }
}
