<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_mistake_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('source_type', 30);
            $table->unsignedBigInteger('source_answer_id');
            $table->text('note')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->unsignedSmallInteger('review_count')->default(0);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'source_type', 'source_answer_id'], 'learning_mistake_review_unique');
        });

        Schema::create('learning_practice_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->cascadeOnDelete();
            $table->string('title');
            $table->string('subject')->nullable()->index();
            $table->string('topic')->nullable()->index();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->nullable();
            $table->enum('source', ['manual', 'weak_topics', 'mistakes'])->default('manual');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['creator_id', 'created_at']);
        });

        Schema::create('learning_practice_set_questions', function (Blueprint $table) {
            $table->foreignId('learning_practice_set_id')->constrained('learning_practice_sets')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('question_bank_questions')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['learning_practice_set_id', 'question_id'], 'learning_practice_question_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_practice_set_questions');
        Schema::dropIfExists('learning_practice_sets');
        Schema::dropIfExists('learning_mistake_reviews');
    }
};
