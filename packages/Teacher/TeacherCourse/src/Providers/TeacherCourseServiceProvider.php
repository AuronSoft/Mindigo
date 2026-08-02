<?php

namespace Mindigo\TeacherCourse\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Mindigo\TeacherCourse\Models\CourseClassroomAssignment;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\CourseReview;
use Mindigo\TeacherCourse\Models\CourseWishlist;
use Mindigo\TeacherCourse\Models\Lesson;
use Mindigo\TeacherCourse\Models\TeacherProfile;
use Mindigo\TeacherCourse\Observers\CoursePlatformAuditObserver;
use Mindigo\TeacherCourse\Policies\ChapterPolicy;
use Mindigo\TeacherCourse\Policies\CourseCategoryPolicy;
use Mindigo\TeacherCourse\Policies\CourseEnrollmentPolicy;
use Mindigo\TeacherCourse\Policies\CoursePolicy;
use Mindigo\TeacherCourse\Policies\CourseReviewPolicy;
use Mindigo\TeacherCourse\Policies\CourseWishlistPolicy;
use Mindigo\TeacherCourse\Policies\LessonPolicy;
use Mindigo\TeacherCourse\Policies\TeacherProfilePolicy;
use Mindigo\TeacherCourse\Services\CourseDiscoveryService;

class TeacherCourseServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(CourseCategory::class, CourseCategoryPolicy::class);
        Gate::policy(CourseEnrollment::class, CourseEnrollmentPolicy::class);
        Gate::policy(CourseReview::class, CourseReviewPolicy::class);
        Gate::policy(Chapter::class, ChapterPolicy::class);
        Gate::policy(Lesson::class, LessonPolicy::class);
        Gate::policy(TeacherProfile::class, TeacherProfilePolicy::class);
        Gate::define('manageWishlist', [CourseWishlistPolicy::class, 'manage']);

        foreach ([Course::class, CourseEnrollment::class, CourseReview::class, CourseWishlist::class, CourseClassroomAssignment::class] as $model) {
            $model::observe(CoursePlatformAuditObserver::class);
        }

        View::composer('core::home', function ($view): void {
            $view->with('featuredCourses', app(CourseDiscoveryService::class)->featured());
        });

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teacher-course');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teacher-course');
    }
}
