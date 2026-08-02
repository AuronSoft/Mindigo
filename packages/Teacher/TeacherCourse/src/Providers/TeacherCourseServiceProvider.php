<?php

namespace Mindigo\TeacherCourse\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\Lesson;
use Mindigo\TeacherCourse\Models\TeacherProfile;
use Mindigo\TeacherCourse\Policies\ChapterPolicy;
use Mindigo\TeacherCourse\Policies\CoursePolicy;
use Mindigo\TeacherCourse\Policies\LessonPolicy;
use Mindigo\TeacherCourse\Policies\TeacherProfilePolicy;

class TeacherCourseServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Chapter::class, ChapterPolicy::class);
        Gate::policy(Lesson::class, LessonPolicy::class);
        Gate::policy(TeacherProfile::class, TeacherProfilePolicy::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teacher-course');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teacher-course');
    }
}
