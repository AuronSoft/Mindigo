<?php

namespace Mindigo\StudentPractice\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Mindigo\StudentPractice\Contracts\PracticeServiceInterface;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeLearningInsight;
use Mindigo\StudentPractice\Models\PracticeSet;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\StudentPractice\Policies\PracticeAttemptPolicy;
use Mindigo\StudentPractice\Policies\PracticeLearningInsightPolicy;
use Mindigo\StudentPractice\Policies\PracticeSetPolicy;
use Mindigo\StudentPractice\Policies\PracticeSkillPolicy;
use Mindigo\StudentPractice\Services\PracticeService;

class StudentPracticeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PracticeServiceInterface::class, PracticeService::class);
    }

    public function boot(): void
    {
        Gate::policy(PracticeAttempt::class, PracticeAttemptPolicy::class);
        Gate::policy(PracticeLearningInsight::class, PracticeLearningInsightPolicy::class);
        Gate::policy(PracticeSet::class, PracticeSetPolicy::class);
        Gate::policy(PracticeSkill::class, PracticeSkillPolicy::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'student-practice');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'student-practice');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/student-practice'),
        ], 'student-practice-views');

        // Publish language files
        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/student-practice'),
        ], 'student-practice-lang');
    }
}
