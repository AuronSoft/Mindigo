<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_sessions', fn (Blueprint $table) => $table->boolean('anonymous_grading')->default(false)->after('result_policy'));
        Schema::table('exam_template_questions', fn (Blueprint $table) => $table->json('rubric')->nullable()->after('explanation'));
        Schema::table('exam_session_attempts', function (Blueprint $table): void {
            $table->string('grading_status', 32)->default('auto_graded')->index()->after('needs_review');
            $table->string('anonymous_code', 24)->nullable()->unique()->after('grading_status');
        });
        Schema::table('exam_session_attempt_answers', fn (Blueprint $table) => $table->json('rubric_scores')->nullable()->after('feedback'));
        DB::table('exam_session_attempts')->where('needs_review', true)->update(['grading_status' => 'pending_manual']);
        DB::table('exam_session_attempts')->where('needs_review', false)->whereNotNull('submitted_at')->update(['grading_status' => 'completed']);

        Schema::create('exam_grading_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grader_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['exam_session_id', 'grader_id']);
        });
        Schema::create('exam_grade_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_attempt_answer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('previous_points', 10, 2);
            $table->decimal('new_points', 10, 2);
            $table->text('previous_feedback')->nullable();
            $table->text('new_feedback')->nullable();
            $table->json('previous_rubric_scores')->nullable();
            $table->json('new_rubric_scores')->nullable();
            $table->string('reason', 500)->nullable();
            $table->timestamps();
        });
        Schema::create('exam_grade_appeals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->string('status', 24)->default('open')->index();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->unique(['exam_session_attempt_id', 'requested_by'], 'exam_attempt_requester_appeal_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_grade_appeals');
        Schema::dropIfExists('exam_grade_revisions');
        Schema::dropIfExists('exam_grading_assignments');
        Schema::table('exam_session_attempt_answers', fn (Blueprint $table) => $table->dropColumn('rubric_scores'));
        Schema::table('exam_session_attempts', fn (Blueprint $table) => $table->dropColumn(['grading_status', 'anonymous_code']));
        Schema::table('exam_template_questions', fn (Blueprint $table) => $table->dropColumn('rubric'));
        Schema::table('exam_sessions', fn (Blueprint $table) => $table->dropColumn('anonymous_grading'));
    }
};
