<?php

namespace Mindigo\TeacherExam\Providers;

use Illuminate\Support\ServiceProvider;

class TeacherExamServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teacher-exam');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teacher-exam');
    }
}
