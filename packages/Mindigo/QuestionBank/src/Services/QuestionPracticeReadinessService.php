<?php

namespace Mindigo\QuestionBank\Services;

use Illuminate\Support\Facades\DB;
use Mindigo\QuestionBank\Models\Question;

class QuestionPracticeReadinessService
{
    public function refresh(Question $question): Question
    {
        $issues = $this->issues($question);
        $ready = $issues === [];

        $question->forceFill([
            'practice_status' => $ready ? Question::PRACTICE_READY : Question::PRACTICE_NEEDS_REVIEW,
            'readiness_issues' => $issues,
            'practice_ready_at' => $ready ? ($question->practice_ready_at ?? now()) : null,
        ])->save();

        return $question->refresh();
    }

    public function issues(Question $question): array
    {
        $issues = [];
        if ($question->status !== 'approved') {
            $issues[] = 'not_approved';
        }
        if (blank(strip_tags((string) $question->content))) {
            $issues[] = 'missing_content';
        }
        if ($question->type === 'essay') {
            $issues[] = 'manual_grading_required';
        }
        if (in_array($question->type, ['single_choice', 'multiple_choice', 'true_false'], true)
            && count($question->options ?? []) < 2) {
            $issues[] = 'missing_options';
        }
        if ($question->type !== 'essay' && empty($question->correct_answers ?? [])) {
            $issues[] = 'missing_correct_answer';
        }
        if (! DB::table('question_practice_skill')->where('question_id', $question->getKey())->exists()) {
            $issues[] = 'missing_skill';
        }

        return $issues;
    }
}
