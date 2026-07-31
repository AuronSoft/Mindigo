<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcard_decks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('visibility', ['private', 'public'])->default('private');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['owner_id', 'updated_at']);
            $table->index(['visibility', 'subject_id']);
        });

        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flashcard_deck_id')->constrained('flashcard_decks')->cascadeOnDelete();
            $table->text('front');
            $table->text('back');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['flashcard_deck_id', 'position']);
        });

        Schema::create('flashcard_deck_classroom', function (Blueprint $table) {
            $table->foreignId('flashcard_deck_id')->constrained('flashcard_decks')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->primary(['flashcard_deck_id', 'classroom_id']);
        });

        Schema::create('flashcard_progress', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('flashcard_id')->constrained('flashcards')->cascadeOnDelete();
            $table->enum('rating', ['again', 'hard', 'good', 'easy'])->nullable();
            $table->unsignedSmallInteger('repetitions')->default(0);
            $table->unsignedSmallInteger('interval_days')->default(0);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->timestamps();
            $table->primary(['user_id', 'flashcard_id']);
            $table->index(['user_id', 'next_review_at']);
        });

        Schema::create('study_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'completed', 'archived'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['creator_id', 'status']);
            $table->index(['classroom_id', 'start_date', 'end_date']);
        });

        Schema::create('study_plan_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_plan_id')->constrained('study_plans')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['study_plan_id', 'position']);
        });

        Schema::create('study_task_completions', function (Blueprint $table) {
            $table->foreignId('study_plan_task_id')->constrained('study_plan_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->timestamps();
            $table->primary(['study_plan_task_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_task_completions');
        Schema::dropIfExists('study_plan_tasks');
        Schema::dropIfExists('study_plans');
        Schema::dropIfExists('flashcard_progress');
        Schema::dropIfExists('flashcard_deck_classroom');
        Schema::dropIfExists('flashcards');
        Schema::dropIfExists('flashcard_decks');
    }
};
