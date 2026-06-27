<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng bài tập
        if (! Schema::hasTable('assignments')) {
            Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();       // đề bài dạng text
            $table->string('file_path')->nullable();       // file đề đính kèm
            $table->dateTime('due_date');
            $table->boolean('allow_late')->default(false);
            $table->unsignedTinyInteger('late_days')->nullable();
            $table->unsignedSmallInteger('max_score')->default(10);

            // 'file' | 'text' | 'both'
            $table->enum('submission_type', ['file', 'text', 'both'])->default('both');

            // 'draft' | 'published'
            $table->enum('status', ['draft', 'published'])->default('draft');

            $table->timestamps();
            $table->softDeletes();
            });
        }

        // Bảng bài nộp
        if (! Schema::hasTable('assignment_submissions')) {
            Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();

            // Hình thức 1: nộp file
            $table->string('file_path')->nullable();
            $table->string('file_original_name')->nullable();  // tên file gốc

            // Hình thức 2: nộp văn bản
            $table->text('text_content')->nullable();

            $table->dateTime('submitted_at')->nullable();
            $table->boolean('is_late')->default(false);

            // Chấm bài
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->dateTime('graded_at')->nullable();

            // 'submitted' | 'graded' | 'returned'
            $table->enum('status', ['submitted', 'graded', 'returned'])->default('submitted');

            $table->timestamps();

            // Mỗi SV chỉ nộp 1 lần / bài tập
            $table->unique(['assignment_id', 'student_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};
