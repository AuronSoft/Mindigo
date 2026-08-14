<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_session_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('attempt_number');
            $table->string('status', 24)->default('in_progress')->index();
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamp('last_activity_at');
            $table->timestamp('submitted_at')->nullable();
            $table->json('question_order');
            $table->json('answer_order')->nullable();
            $table->json('security_events')->nullable();
            $table->timestamps();

            $table->unique(['exam_session_id', 'user_id', 'attempt_number'], 'exam_session_user_attempt_unique');
            $table->index(['exam_session_id', 'user_id', 'status'], 'exam_session_user_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_session_attempts');
    }
};
