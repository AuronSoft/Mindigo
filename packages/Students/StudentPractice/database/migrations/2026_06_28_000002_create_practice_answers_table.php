<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_practice_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('student_practice_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('question_bank_questions')->onDelete('restrict');
            $table->json('student_answer')->nullable(); // lưu trữ câu trả lời của học sinh
            $table->boolean('is_correct')->default(false);
            $table->float('points')->default(0); // điểm cho câu này
            $table->timestamps();
            $table->unique(['attempt_id', 'question_id']);
            $table->index(['attempt_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_practice_answers');
    }
};
