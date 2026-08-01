<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->unsignedInteger('question_pool_size')->default(0)->after('difficulty');
            $table->string('selection_strategy', 30)->default('balanced')->after('question_pool_size');
        });

        Schema::table('student_practice_answers', function (Blueprint $table): void {
            $table->json('question_snapshot')->nullable()->after('question_id');
            $table->string('difficulty_snapshot', 30)->nullable()->after('question_snapshot');
            $table->unsignedSmallInteger('response_seconds')->nullable()->after('points');
            $table->unsignedInteger('answer_revision')->default(0)->after('response_seconds');
            $table->timestamp('answered_at')->nullable()->after('answer_revision');
        });

        Schema::create('student_skill_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('practice_skill_id')->constrained('practice_skills')->cascadeOnDelete();
            $table->unsignedInteger('completed_attempts')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->decimal('average_score', 5, 2)->default(0);
            $table->decimal('best_score', 5, 2)->default(0);
            $table->unsignedInteger('practice_seconds')->default(0);
            $table->timestamp('last_practiced_at')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'practice_skill_id'], 'student_skill_progress_unique');
            $table->index(['student_id', 'last_practiced_at'], 'student_skill_progress_recent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_skill_progress');
        Schema::table('student_practice_answers', function (Blueprint $table): void {
            $table->dropColumn(['question_snapshot', 'difficulty_snapshot', 'response_seconds', 'answer_revision', 'answered_at']);
        });
        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->dropColumn(['question_pool_size', 'selection_strategy']);
        });
    }
};
