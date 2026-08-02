<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_classroom_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamps();
            $table->unique(['course_id', 'classroom_id'], 'course_classroom_unique');
        });

        Schema::create('course_enrollments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
            $table->string('status', 30)->default('enrolled');
            $table->string('source', 20)->default('self');
            $table->unsignedTinyInteger('completion_percentage')->default(0);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->unique(['course_id', 'student_id'], 'course_student_enrollment_unique');
            $table->index(['student_id', 'status', 'last_activity_at'], 'student_course_activity_index');
        });

        Schema::create('course_lesson_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('course_enrollments')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->timestamp('first_viewed_at')->nullable();
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['enrollment_id', 'lesson_id'], 'enrollment_lesson_progress_unique');
            $table->index(['enrollment_id', 'completed_at'], 'enrollment_completion_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_lesson_progress');
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('course_classroom_assignments');
    }
};
