<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subject')->nullable()->index();
            $table->string('topic')->nullable()->index();
            $table->enum('status', ['draft', 'reviewing', 'published', 'closed'])->default('draft')->index();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(45);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('max_attempts')->default(1);
            $table->decimal('passing_score', 8, 2)->default(0);
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_answers')->default(true);
            $table->boolean('show_results')->default(true);
            $table->json('audience')->nullable();
            $table->json('generation_config')->nullable();
            $table->unsignedInteger('total_questions')->default(0);
            $table->decimal('total_points', 8, 2)->default(0);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('question_id')->nullable()->constrained('question_bank_questions')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('subject')->nullable();
            $table->string('topic')->nullable();
            $table->enum('type', ['single_choice', 'multiple_choice', 'true_false', 'short_answer', 'essay'])->default('single_choice');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->text('content');
            $table->json('options')->nullable();
            $table->json('correct_answers')->nullable();
            $table->text('explanation')->nullable();
            $table->decimal('points', 8, 2)->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['exam_id', 'sort_order']);
            $table->index(['exam_id', 'type']);
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['in_progress', 'submitted', 'expired'])->default('in_progress')->index();
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 8, 2)->default(0);
            $table->decimal('max_score', 8, 2)->default(0);
            $table->decimal('percentage', 6, 2)->default(0);
            $table->boolean('passed')->default(false);
            $table->unsignedInteger('tab_leave_count')->default(0);
            $table->json('question_order')->nullable();
            $table->json('autosave_payload')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'user_id', 'status']);
        });

        Schema::create('exam_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('exam_question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->enum('type', ['single_choice', 'multiple_choice', 'true_false', 'short_answer', 'essay'])->default('single_choice');
            $table->json('answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_awarded', 8, 2)->default(0);
            $table->boolean('needs_review')->default(false);
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'exam_question_id'], 'exam_attempt_answer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempt_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
    }
};
