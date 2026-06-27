<?php

namespace Mindigo\StudentAssignment\Providers;

use Illuminate\Support\ServiceProvider;

class StudentAssignmentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'student-assignment');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'student-assignment');
    }
}
