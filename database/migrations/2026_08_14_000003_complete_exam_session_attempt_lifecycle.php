<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_session_attempts', function (Blueprint $table): void {
            $table->decimal('score', 10, 2)->default(0)->after('submitted_at');
            $table->decimal('max_score', 10, 2)->default(0)->after('score');
            $table->decimal('percentage', 6, 2)->default(0)->after('max_score');
            $table->boolean('passed')->nullable()->after('percentage');
            $table->boolean('needs_review')->default(false)->after('passed');
        });

        Schema::create('exam_session_attempt_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_template_question_id')->constrained()->restrictOnDelete();
            $table->string('type', 32);
            $table->json('answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_awarded', 10, 2)->default(0);
            $table->boolean('needs_review')->default(false);
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['exam_session_attempt_id', 'exam_template_question_id'], 'exam_session_attempt_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_session_attempt_answers');
        Schema::table('exam_session_attempts', function (Blueprint $table): void {
            $table->dropColumn(['score', 'max_score', 'percentage', 'passed', 'needs_review']);
        });
    }
};
