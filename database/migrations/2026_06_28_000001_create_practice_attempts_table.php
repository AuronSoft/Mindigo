<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_practice_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->enum('mode', ['subject', 'topic', 'mixed'])->default('mixed');
            $table->string('subject')->nullable();
            $table->string('topic')->nullable();
            $table->string('difficulty')->nullable(); // 'easy', 'medium', 'hard', or null for mixed
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->float('score')->nullable(); // percentage 0-100
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_practice_attempts');
    }
};
