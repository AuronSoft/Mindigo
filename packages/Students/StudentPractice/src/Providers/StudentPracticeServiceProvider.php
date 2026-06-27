<?php

namespace Mindigo\StudentPractice\Providers;

use Illuminate\Support\ServiceProvider;

class StudentPracticeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'student-practice');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'student-practice');
    }
}
