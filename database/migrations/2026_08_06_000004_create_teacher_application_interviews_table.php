<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_application_interviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_application_id')->constrained('teacher_applications')->cascadeOnDelete();
            $table->foreignId('interviewer_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('scheduled_at')->index();
            $table->string('mode')->index();
            $table->string('meeting_url')->nullable();
            $table->text('pre_interview_note')->nullable();
            $table->unsignedTinyInteger('subject_knowledge_score')->nullable();
            $table->unsignedTinyInteger('pedagogy_score')->nullable();
            $table->unsignedTinyInteger('communication_score')->nullable();
            $table->unsignedTinyInteger('lms_technology_score')->nullable();
            $table->text('overall_comment')->nullable();
            $table->string('result')->nullable()->index();
            $table->timestamp('evaluated_at')->nullable()->index();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['teacher_application_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_application_interviews');
    }
};
