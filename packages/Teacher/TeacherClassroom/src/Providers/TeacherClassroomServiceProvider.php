<?php

namespace Mindigo\TeacherClassroom\Providers;

use Illuminate\Support\ServiceProvider;

class TeacherClassroomServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'teacher-classroom');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'teacher-classroom');
    }
}
