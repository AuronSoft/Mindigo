<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->index(['teacher_id', 'publication_status', 'created_at'], 'courses_teacher_publication_created_index');
            $table->index(['category_id', 'subject_id', 'publication_status'], 'courses_taxonomy_publication_index');
        });
        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->index(['course_id', 'status', 'last_activity_at'], 'course_enrollment_analytics_index');
            $table->index(['classroom_id', 'status', 'completed_at'], 'classroom_completion_analytics_index');
        });
        Schema::table('course_lesson_progress', function (Blueprint $table): void {
            $table->index(['lesson_id', 'completed_at'], 'lesson_completion_analytics_index');
        });
    }

    public function down(): void
    {
        Schema::table('course_lesson_progress', fn (Blueprint $table) => $table->dropIndex('lesson_completion_analytics_index'));
        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->dropIndex('course_enrollment_analytics_index');
            $table->dropIndex('classroom_completion_analytics_index');
        });
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropIndex('courses_teacher_publication_created_index');
            $table->dropIndex('courses_taxonomy_publication_index');
        });
    }
};
