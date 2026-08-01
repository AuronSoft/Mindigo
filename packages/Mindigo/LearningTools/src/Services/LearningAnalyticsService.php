<?php

namespace Mindigo\LearningTools\Services;

use Illuminate\Support\Collection;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\LearningTools\Models\MistakeReview;
use Mindigo\StudentPractice\Models\PracticeAnswer;
use Mindigo\StudentPractice\Models\PracticeAttempt;

class LearningAnalyticsService
{
    public function mistakes(int $userId): Collection
    {
        $reviews = MistakeReview::where('user_id', $userId)->get()->keyBy(fn ($row) => $row->source_type.':'.$row->source_answer_id);

        $practice = PracticeAnswer::with(['question', 'attempt'])
            ->where('is_correct', false)
            ->whereHas('attempt', fn ($query) => $query->where('student_id', $userId)->where('status', PracticeAttempt::STATUS_COMPLETED))
            ->latest()->get()->map(fn ($answer) => $this->mistakeRow('practice', $answer->id, $answer->question, $answer->student_answer, $answer->created_at, $reviews));

        $exam = ExamAttemptAnswer::with(['question', 'attempt'])
            ->where('is_correct', false)
            ->whereHas('attempt', fn ($query) => $query->where('user_id', $userId)->whereIn('status', ['submitted', 'expired']))
            ->latest()->get()->map(fn ($answer) => $this->mistakeRow('exam', $answer->id, $answer->question, $answer->answer, $answer->created_at, $reviews));

        return $practice->concat($exam)->sortByDesc('answered_at')->values();
    }

    public function gapsForStudent(int $userId): Collection
    {
        $rows = collect();
        PracticeAnswer::with(['question:id,subject,topic', 'attempt:id,student_id'])
            ->whereHas('attempt', fn ($query) => $query->where('student_id', $userId)->where('status', PracticeAttempt::STATUS_COMPLETED))
            ->get()->each(function ($answer) use ($rows): void {
                $rows->push(['subject' => $answer->question?->subject, 'topic' => $answer->question?->topic, 'correct' => (bool) $answer->is_correct]);
            });
        ExamAttemptAnswer::with(['question:id,subject,topic', 'attempt:id,user_id,status'])
            ->whereHas('attempt', fn ($query) => $query->where('user_id', $userId)->whereIn('status', ['submitted', 'expired']))
            ->get()->each(function ($answer) use ($rows): void {
                $rows->push(['subject' => $answer->question?->subject, 'topic' => $answer->question?->topic, 'correct' => (bool) $answer->is_correct]);
            });

        return $rows->filter(fn ($row) => filled($row['subject']) || filled($row['topic']))
            ->groupBy(fn ($row) => ($row['subject'] ?: '-').'|'.($row['topic'] ?: '-'))
            ->map(function (Collection $group): array {
                $total = $group->count();
                $correct = $group->where('correct', true)->count();
                $rate = $total ? round($correct / $total * 100, 1) : 0;

                return [...$group->first(), 'total' => $total, 'correct_count' => $correct, 'rate' => $rate, 'level' => $rate < 50 ? 'weak' : ($rate < 75 ? 'average' : 'strong')];
            })->sortBy('rate')->values();
    }

    private function mistakeRow(string $type, int $id, mixed $question, mixed $answer, mixed $answeredAt, Collection $reviews): array
    {
        $review = $reviews->get($type.':'.$id);

        return [
            'source_type' => $type,
            'source_answer_id' => $id,
            'question_id' => $question?->question_id ?? $question?->id,
            'content' => $question?->content,
            'subject' => $question?->subject,
            'topic' => $question?->topic,
            'difficulty' => $question?->difficulty,
            'student_answer' => $answer,
            'correct_answers' => $question?->correct_answers,
            'explanation' => $question?->explanation,
            'answered_at' => $answeredAt,
            'review' => $review,
        ];
    }
}
