<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExamInventoryService
{
    /** @return array<string, mixed> */
    public function collect(): array
    {
        $tables = [
            'exams',
            'exam_questions',
            'exam_attempts',
            'exam_attempt_answers',
            'exam_templates',
            'exam_template_versions',
            'exam_sections',
            'exam_template_questions',
            'exam_sessions',
            'exam_assignments',
            'exam_candidates',
            'exam_session_attempts',
            'exam_session_attempt_answers',
            'exam_proctor_events',
            'exam_migration_runs',
        ];

        $counts = collect($tables)->mapWithKeys(fn (string $table): array => [
            $table => Schema::hasTable($table) ? DB::table($table)->count() : null,
        ])->all();

        $statuses = Schema::hasTable('exams')
            ? DB::table('exams')->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status')->all()
            : [];

        $orphaned = Schema::hasTable('exam_attempts') && Schema::hasTable('exams')
            ? DB::table('exam_attempts')->leftJoin('exams', 'exams.id', '=', 'exam_attempts.exam_id')->whereNull('exams.id')->count()
            : null;

        return [
            'schema' => 'exam',
            'generated_at' => now()->toIso8601String(),
            'tables' => $counts,
            'exam_statuses' => $statuses,
            'orphaned_attempts' => $orphaned,
            'legacy_mapping' => [
                'templates' => Schema::hasColumn('exam_templates', 'legacy_exam_id') ? DB::table('exam_templates')->whereNotNull('legacy_exam_id')->count() : null,
                'sessions' => Schema::hasColumn('exam_sessions', 'legacy_exam_id') ? DB::table('exam_sessions')->whereNotNull('legacy_exam_id')->count() : null,
                'attempts' => Schema::hasColumn('exam_session_attempts', 'legacy_exam_attempt_id') ? DB::table('exam_session_attempts')->whereNotNull('legacy_exam_attempt_id')->count() : null,
                'answers' => Schema::hasColumn('exam_session_attempt_answers', 'legacy_exam_attempt_answer_id') ? DB::table('exam_session_attempt_answers')->whereNotNull('legacy_exam_attempt_answer_id')->count() : null,
            ],
        ];
    }
}
