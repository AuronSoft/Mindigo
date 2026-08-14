<?php

namespace Mindigo\ExamManagement\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Policies\ExamSessionPolicy;
use Mindigo\ExamManagement\Policies\ExamTemplatePolicy;

class ExamManagementServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(ExamTemplate::class, ExamTemplatePolicy::class);
        Gate::policy(ExamSession::class, ExamSessionPolicy::class);
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'Mindigo-exam-management');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'Mindigo-exam-management');

        $this->callAfterResolving(BladeCompiler::class, function (BladeCompiler $blade): void {
            $blade->anonymousComponentNamespace('Mindigo-exam-management::components', 'exam');
        });
    }
}
