<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureTrackingColumn('exam_templates', 'legacy_exam_id', 'exams', 'exam_tpl_legacy_fk', 'exam_tpl_legacy_uq');
        $this->ensureTrackingColumn('exam_sessions', 'legacy_exam_id', 'exams', 'exam_session_legacy_fk', 'exam_session_legacy_uq');
        $this->ensureTrackingColumn('exam_template_questions', 'legacy_exam_question_id', 'exam_questions', 'exam_tpl_question_legacy_fk', 'exam_tpl_question_legacy_uq');
        $this->ensureTrackingColumn('exam_session_attempts', 'legacy_exam_attempt_id', 'exam_attempts', 'exam_session_attempt_legacy_fk', 'exam_session_attempt_legacy_uq');
        $this->ensureTrackingColumn('exam_session_attempt_answers', 'legacy_exam_attempt_answer_id', 'exam_attempt_answers', 'exam_attempt_answer_legacy_fk', 'exam_attempt_answer_legacy_uq');

        Schema::create('exam_migration_runs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('mode', 24);
            $table->string('status', 24)->index();
            $table->json('legacy_exam_ids')->nullable();
            $table->json('summary')->nullable();
            $table->json('issues')->nullable();
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_migration_runs');
        $this->dropTrackingColumn('exam_session_attempt_answers', 'legacy_exam_attempt_answer_id');
        $this->dropTrackingColumn('exam_session_attempts', 'legacy_exam_attempt_id');
        $this->dropTrackingColumn('exam_template_questions', 'legacy_exam_question_id');
        $this->dropTrackingColumn('exam_sessions', 'legacy_exam_id');
        $this->dropTrackingColumn('exam_templates', 'legacy_exam_id');
    }

    private function ensureTrackingColumn(string $tableName, string $column, string $references, string $foreignName, string $uniqueName): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            Schema::table($tableName, fn (Blueprint $table) => $table->unsignedBigInteger($column)->nullable()->after('id'));
        }

        $hasUnique = collect(Schema::getIndexes($tableName))->contains(fn (array $index): bool => ($index['unique'] ?? false) && ($index['columns'] ?? []) === [$column]);
        if (! $hasUnique) {
            Schema::table($tableName, fn (Blueprint $table) => $table->unique($column, $uniqueName));
        }

        $hasForeign = collect(Schema::getForeignKeys($tableName))->contains(fn (array $foreign): bool => ($foreign['columns'] ?? []) === [$column]);
        if (! $hasForeign) {
            Schema::table($tableName, fn (Blueprint $table) => $table->foreign($column, $foreignName)->references('id')->on($references)->nullOnDelete());
        }
    }

    private function dropTrackingColumn(string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            return;
        }

        $foreign = collect(Schema::getForeignKeys($tableName))->first(fn (array $key): bool => ($key['columns'] ?? []) === [$column]);
        if ($foreign) {
            Schema::table($tableName, fn (Blueprint $table) => $table->dropForeign($foreign['name']));
        }
        Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn($column));
    }
};
