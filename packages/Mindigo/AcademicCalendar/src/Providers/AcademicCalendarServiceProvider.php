<?php

namespace Mindigo\AcademicCalendar\Providers;

use Illuminate\Support\ServiceProvider;
use Mindigo\AcademicCalendar\Adapters\AssignmentAdapter;
use Mindigo\AcademicCalendar\Adapters\ClassroomScheduleAdapter;
use Mindigo\AcademicCalendar\Adapters\ExamAdapter;
use Mindigo\AcademicCalendar\Adapters\LiveSessionAdapter;
use Mindigo\AcademicCalendar\Contracts\CalendarSourceAdapter;
use Mindigo\AcademicCalendar\Services\AcademicCalendarService;

final class AcademicCalendarServiceProvider extends ServiceProvider
{
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
