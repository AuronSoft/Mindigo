<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subject')->nullable()->index();
            $table->string('topic')->nullable()->index();
            $table->string('status', 24)->default('draft')->index();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedInteger('current_version')->default(1);
            $table->unsignedInteger('total_questions')->default(0);
            $table->decimal('total_points', 10, 2)->default(0);
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exam_template_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedInteger('total_questions')->default(0);
            $table->decimal('total_points', 10, 2)->default(0);
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->unique(['exam_template_id', 'version']);
        });

        Schema::create('exam_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_template_version_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('shuffle_questions')->default(false);
            $table->timestamps();
            $table->index(['exam_template_version_id', 'sort_order']);
        });

        Schema::create('exam_template_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_template_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('source_question_id')->nullable()->constrained('question_bank_questions')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('type', 32);
            $table->string('difficulty', 24)->nullable();
            $table->text('content');
            $table->json('options')->nullable();
            $table->json('correct_answers')->nullable();
            $table->text('explanation')->nullable();
            $table->decimal('points', 10, 2)->default(1);
            $table->timestamps();
            $table->index(['exam_template_version_id', 'sort_order'], 'exam_template_question_order_index');
        });

        Schema::create('exam_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_template_version_id')->constrained()->restrictOnDelete();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status', 24)->default('draft')->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedSmallInteger('max_attempts')->default(1);
            $table->decimal('passing_score', 10, 2)->default(0);
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_answers')->default(true);
            $table->string('result_policy', 24)->default('after_release');
            $table->json('security_policy')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exam_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->morphs('assignable');
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamps();
            $table->unique(['exam_session_id', 'assignable_type', 'assignable_id'], 'exam_assignment_unique');
        });

        Schema::create('exam_candidates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('student_code')->nullable();
            $table->string('status', 24)->default('eligible')->index();
            $table->unsignedSmallInteger('extra_time_minutes')->default(0);
            $table->unsignedSmallInteger('max_attempts_override')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['exam_session_id', 'user_id']);
            $table->index(['exam_session_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_candidates');
        Schema::dropIfExists('exam_assignments');
        Schema::dropIfExists('exam_sessions');
        Schema::dropIfExists('exam_template_questions');
        Schema::dropIfExists('exam_sections');
        Schema::dropIfExists('exam_template_versions');
        Schema::dropIfExists('exam_templates');
    }
};
