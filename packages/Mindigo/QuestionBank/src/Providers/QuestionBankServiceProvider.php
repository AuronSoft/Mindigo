<?php

namespace Mindigo\QuestionBank\Providers;

use Illuminate\Support\ServiceProvider;

class QuestionBankServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Mindigo-question-bank');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Mindigo-question-bank');
    }
}
