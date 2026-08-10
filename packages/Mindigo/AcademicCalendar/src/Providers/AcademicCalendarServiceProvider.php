<?php

namespace Mindigo\AcademicCalendar\Providers;

use Illuminate\Support\ServiceProvider;
use Mindigo\AcademicCalendar\Adapters\AssignmentAdapter;
use Mindigo\AcademicCalendar\Adapters\ClassroomScheduleAdapter;
use Mindigo\AcademicCalendar\Adapters\ExamAdapter;
use Mindigo\AcademicCalendar\Adapters\LiveSessionAdapter;
use Mindigo\AcademicCalendar\Console\SendCalendarReminders;
use Mindigo\AcademicCalendar\Contracts\CalendarSourceAdapter;
use Mindigo\AcademicCalendar\Observers\ClassroomScheduleObserver;
use Mindigo\AcademicCalendar\Services\AcademicCalendarService;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

final class AcademicCalendarServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'academic-calendar');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'academic-calendar');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        ClassroomSchedule::observe(ClassroomScheduleObserver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([SendCalendarReminders::class]);
        }
    }

    public function register(): void
    {
        $adapters = [
            ClassroomScheduleAdapter::class,
            AssignmentAdapter::class,
            ExamAdapter::class,
            LiveSessionAdapter::class,
        ];

        $this->app->tag($adapters, CalendarSourceAdapter::class);
        $this->app->singleton(
            AcademicCalendarService::class,
            fn ($app) => new AcademicCalendarService($app->tagged(CalendarSourceAdapter::class)),
        );
    }
}
