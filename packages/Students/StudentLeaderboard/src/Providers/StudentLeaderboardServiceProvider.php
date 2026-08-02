<?php

namespace Mindigo\StudentLeaderboard\Providers;

use Illuminate\Support\ServiceProvider;

class StudentLeaderboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'student-leaderboard');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'student-leaderboard');
    }
}
