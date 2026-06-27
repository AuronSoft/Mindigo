<?php

namespace Mindigo\TeacherAssignment\Providers;

use Illuminate\Support\ServiceProvider;

class TeacherAssignmentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'teacher-assignment');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'teacher-assignment');
    }
}
