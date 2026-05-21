<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_bank_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject')->index();
            $table->string('topic')->nullable()->index();
            $table->enum('type', ['single_choice', 'multiple_choice', 'true_false', 'short_answer'])->default('single_choice')->index();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->index();
            $table->enum('status', ['draft', 'reviewing', 'approved', 'rejected'])->default('draft')->index();
            $table->text('content');
            $table->json('options')->nullable();
            $table->json('correct_answers')->nullable();
            $table->text('explanation')->nullable();
            $table->json('tags')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['subject', 'status']);
            $table->index(['created_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_bank_questions');
    }
};
