<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_skills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('subject_topic_id')->nullable()->constrained('subject_topics')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('practice_skills')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code', 80)->unique();
            $table->string('name', 180);
            $table->string('grade_level', 40)->nullable()->index();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['subject_id', 'subject_topic_id', 'status'], 'practice_skill_catalog_index');
        });

        Schema::create('question_practice_skill', function (Blueprint $table): void {
            $table->foreignId('question_id')->constrained('question_bank_questions')->cascadeOnDelete();
            $table->foreignId('practice_skill_id')->constrained('practice_skills')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->unsignedTinyInteger('weight')->default(100);
            $table->primary(['question_id', 'practice_skill_id'], 'question_practice_skill_primary');
        });

        Schema::table('question_bank_questions', function (Blueprint $table): void {
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('subject_topic_id')->nullable()->constrained('subject_topics')->nullOnDelete();
            $table->string('grade_level', 40)->nullable()->index();
            $table->unsignedSmallInteger('estimated_seconds')->nullable();
            $table->text('hint')->nullable();
            $table->string('practice_status', 20)->default('needs_review')->index();
            $table->json('readiness_issues')->nullable();
            $table->timestamp('practice_ready_at')->nullable();
        });

        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->foreignId('practice_skill_id')->nullable()->after('practice_set_id')
                ->constrained('practice_skills')->nullOnDelete();
        });

        DB::table('question_bank_questions')
            ->where('status', 'approved')
            ->update([
                'practice_status' => 'ready',
                'practice_ready_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('practice_skill_id');
        });

        Schema::table('question_bank_questions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('subject_id');
            $table->dropConstrainedForeignId('subject_topic_id');
            $table->dropColumn([
                'grade_level',
                'estimated_seconds',
                'hint',
                'practice_status',
                'readiness_issues',
                'practice_ready_at',
            ]);
        });

        Schema::dropIfExists('question_practice_skill');
        Schema::dropIfExists('practice_skills');
    }
};
