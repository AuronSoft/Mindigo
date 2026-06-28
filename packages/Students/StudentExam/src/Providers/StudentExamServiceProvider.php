<?php

namespace Mindigo\StudentExam\Providers;

use Illuminate\Support\ServiceProvider;

class StudentExamServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'student-exam');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'student-exam');
    }
}