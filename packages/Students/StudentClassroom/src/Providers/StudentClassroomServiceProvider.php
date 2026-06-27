<?php

namespace Mindigo\StudentClassroom\Providers;

use Illuminate\Support\ServiceProvider;

class StudentClassroomServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'student-classroom');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'student-classroom');
    }
}
