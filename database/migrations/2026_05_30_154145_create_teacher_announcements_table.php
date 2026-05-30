<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('content');
            $table->enum('type', ['info', 'warning', 'reminder', 'assignment'])->default('info');
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['teacher_id', 'created_at']);
        });

        Schema::create('announcement_classroom', function (Blueprint $table) {
            $table->foreignId('announcement_id')->constrained('teacher_announcements')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->primary(['announcement_id', 'classroom_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_classroom');
        Schema::dropIfExists('teacher_announcements');
    }
};
