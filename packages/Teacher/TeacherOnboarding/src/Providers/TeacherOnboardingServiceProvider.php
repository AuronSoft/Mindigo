<?php

namespace Mindigo\TeacherOnboarding\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Mindigo\TeacherOnboarding\Policies\TeacherApplicationPolicy;

class TeacherOnboardingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(TeacherApplication::class, TeacherApplicationPolicy::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teacher-onboarding');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teacher-onboarding');
    }
}
