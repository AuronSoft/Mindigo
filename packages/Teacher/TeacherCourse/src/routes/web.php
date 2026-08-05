<?php

use Illuminate\Support\Facades\Route;
use Mindigo\TeacherCourse\Http\Controllers\AdminCourseReviewController;
use Mindigo\TeacherCourse\Http\Controllers\ChapterController;
use Mindigo\TeacherCourse\Http\Controllers\CourseAnalyticsController;
use Mindigo\TeacherCourse\Http\Controllers\CourseBuilderController;
use Mindigo\TeacherCourse\Http\Controllers\CourseCategoryController;
use Mindigo\TeacherCourse\Http\Controllers\CourseController;
use Mindigo\TeacherCourse\Http\Controllers\CourseDiscoveryController;
use Mindigo\TeacherCourse\Http\Controllers\CourseEnrollmentController;
use Mindigo\TeacherCourse\Http\Controllers\CourseLessonController;
use Mindigo\TeacherCourse\Http\Controllers\CourseMonitoringController;
use Mindigo\TeacherCourse\Http\Controllers\CoursePublicationController;
use Mindigo\TeacherCourse\Http\Controllers\CourseReportController;
use Mindigo\TeacherCourse\Http\Controllers\CourseReviewController;
use Mindigo\TeacherCourse\Http\Controllers\FeaturedCourseController;
use Mindigo\TeacherCourse\Http\Controllers\LessonController;
use Mindigo\TeacherCourse\Http\Controllers\PublicCourseController;
use Mindigo\TeacherCourse\Http\Controllers\StudentCourseController;
use Mindigo\TeacherCourse\Http\Controllers\TeacherProfileController;

Route::middleware('web')->group(function (): void {
    Route::get('/courses', [PublicCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/search/suggestions', [CourseDiscoveryController::class, 'suggestions'])->name('courses.search.suggestions');
    Route::get('/teachers/{teacher}', [TeacherProfileController::class, 'show'])->name('teachers.show');
    Route::get('/courses/{course}', [PublicCourseController::class, 'show'])
        ->middleware('auth')
        ->name('courses.show');
    Route::post('/courses/{course}/enroll', [CourseEnrollmentController::class, 'store'])
        ->middleware(['auth', 'role:student'])
        ->name('courses.enroll');
    Route::middleware('auth')->prefix('/courses/{course}/lessons/{lesson}')->name('courses.lessons.')->group(function (): void {
        Route::get('/', [CourseLessonController::class, 'show'])->name('show');
        Route::get('/video', [CourseLessonController::class, 'video'])->name('video');
        Route::get('/attachments/{attachment}', [CourseLessonController::class, 'attachment'])->name('attachments.show');
    });
});

Route::middleware(['web', 'auth', 'role:student'])->group(function (): void {
    Route::get('/student/wishlist', [CourseDiscoveryController::class, 'wishlist'])->name('student.wishlist.index');
    Route::get('/student/courses-recent', [CourseDiscoveryController::class, 'recent'])->name('student.courses.recent');
    Route::get('/student/courses-recommended', [CourseDiscoveryController::class, 'recommended'])->name('student.courses.recommended');
    Route::post('/courses/{course}/wishlist', [CourseDiscoveryController::class, 'store'])->name('courses.wishlist.store');
    Route::delete('/courses/{course}/wishlist', [CourseDiscoveryController::class, 'destroy'])->name('courses.wishlist.destroy');
});

Route::middleware(['web', 'auth', 'role:student', 'throttle:10,1'])->scopeBindings()->group(function (): void {
    Route::post('/courses/{course}/reviews', [CourseReviewController::class, 'store'])->name('courses.reviews.store');
    Route::put('/courses/{course}/reviews/{review}', [CourseReviewController::class, 'update'])->name('courses.reviews.update');
});

Route::middleware(['web', 'auth', 'role:teacher|admin', 'throttle:20,1'])->group(function (): void {
    Route::post('/course-reviews/{review}/reply', [CourseReviewController::class, 'reply'])->name('course-reviews.reply');
    Route::get('/course-platform/analytics', CourseAnalyticsController::class)->name('course-platform.analytics');
    Route::get('/course-platform/reports/export', CourseReportController::class)->name('course-platform.reports.export');
});

Route::middleware(['web', 'auth', 'role:teacher'])->group(function (): void {
    Route::get('/teacher/public-profile/edit', [TeacherProfileController::class, 'edit'])->name('teacher.profile.edit');
    Route::put('/teacher/public-profile/{profile}', [TeacherProfileController::class, 'update'])->name('teacher.profile.update');
});

Route::middleware(['web', 'auth', 'role:admin'])->group(function (): void {
    Route::get('/admin/course-publication-reviews', [AdminCourseReviewController::class, 'index'])->name('admin.course-publication-reviews.index');
    Route::get('/admin/course-publication-reviews/{course}', [AdminCourseReviewController::class, 'show'])->name('admin.course-publication-reviews.show');
    Route::patch('/admin/course-publication-reviews/{course}', [AdminCourseReviewController::class, 'update'])->name('admin.course-publication-reviews.update');
    Route::resource('/admin/course-categories', CourseCategoryController::class)
        ->except(['show'])
        ->names('admin.course-categories');
    Route::get('/admin/course-reviews', [CourseReviewController::class, 'index'])->name('admin.course-reviews.index');
    Route::patch('/admin/course-reviews/{review}/moderate', [CourseReviewController::class, 'moderate'])->name('admin.course-reviews.moderate');
    Route::patch('/admin/courses/{course}/featured', FeaturedCourseController::class)->name('admin.courses.featured');
});

Route::middleware(['web', 'auth', 'role:student'])
    ->prefix('student/courses')
    ->name('student.courses.')
    ->group(function (): void {
        Route::get('/', [StudentCourseController::class, 'index'])->name('index');
        Route::get('/{course}', [StudentCourseController::class, 'show'])->name('show');
        Route::get('/{course}/lessons/{lesson}', [StudentCourseController::class, 'lesson'])->name('lessons.show');
        Route::post('/{course}/lessons/{lesson}/activity', [StudentCourseController::class, 'activity'])->name('lessons.activity');
        Route::post('/{course}/lessons/{lesson}/complete', [StudentCourseController::class, 'complete'])->name('lessons.complete');
    });

Route::middleware(['web', 'auth', 'role:teacher|admin'])
    ->prefix('teacher/courses')
    ->name('teacher.courses.')
    ->scopeBindings()
    ->group(function () {

        // Courses
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/create', [CourseController::class, 'create'])->name('create');
        Route::post('/', [CourseController::class, 'store'])->name('store');
        Route::get('/{course}', [CourseController::class, 'show'])->name('show');
        Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('edit');
        Route::put('/{course}', [CourseController::class, 'update'])->name('update');
        Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');
        Route::patch('/{course}/publication', [CoursePublicationController::class, 'update'])->name('publication.update');
        Route::post('/{course}/assign', [CourseEnrollmentController::class, 'assign'])->name('assign');
        Route::post('/{course}/duplicate', [CourseBuilderController::class, 'duplicate'])->name('duplicate');
        Route::patch('/{course}/curriculum-order', [CourseBuilderController::class, 'reorder'])->name('curriculum.reorder');
        Route::get('/{course}/monitor', CourseMonitoringController::class)->name('monitor');

        // Chapters
        Route::post('/{course}/chapters', [ChapterController::class, 'store'])->name('chapters.store');
        Route::put('/{course}/chapters/{chapter}', [ChapterController::class, 'update'])->name('chapters.update');
        Route::delete('/{course}/chapters/{chapter}', [ChapterController::class, 'destroy'])->name('chapters.destroy');

        // Lessons
        Route::get('/{course}/chapters/{chapter}/lessons/create', [LessonController::class, 'create'])->name('lessons.create');
        Route::post('/{course}/chapters/{chapter}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::get('/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('lessons.edit');
        Route::post('/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
    });
