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
        ];
    }
}
