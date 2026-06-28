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
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'student-practice-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/student-practice'),
        ], 'student-practice-views');

        // Publish language files
        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/student-practice'),
        ], 'student-practice-lang');
    }
}
