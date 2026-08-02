<?php

namespace Mindigo\SubjectManagement\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\SubjectManagement\Policies\SubjectPolicy;

class SubjectManagementServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Subject::class, SubjectPolicy::class);
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'Mindigo-subject-management');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'Mindigo-subject-management');
    }
}
