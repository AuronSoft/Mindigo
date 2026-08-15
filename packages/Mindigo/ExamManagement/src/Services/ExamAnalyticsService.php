<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamProctorEvent;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\TeacherClassroom\Models\Classroom;

class ExamAnalyticsService
{
    public function report(ExamSession $session, User $teacher): array
    {
        abort_unless($teacher->isTeacher() && (int) $session->organizer_id === (int) $teacher->getAuthIdentifier(), 403);

        $session->load(['version.questions', 'candidates', 'assignments.assignable']);
        $attempts = $session->attempts()->with(['candidate', 'user:id,name,email'])->whereIn('status', [
            ExamSessionAttempt::STATUS_SUBMITTED,
            ExamSessionAttempt::STATUS_EXPIRED,
            ExamSessionAttempt::STATUS_TERMINATED,
        ])->get();
        $answers = ExamSessionAttemptAnswer::query()
            ->with('question')
            ->whereHas('attempt', fn ($query) => $query->where('exam_session_id', $session->id))
            ->get();

        return [
            'session' => $session,
            'summary' => $this->summary($session, $attempts),
            'distribution' => $this->distribution($attempts),
            'questions' => $this->questions($session, $answers, $attempts->count()),
            'supportStudents' => $this->supportStudents($attempts),
            'classrooms' => $this->classrooms($session, $attempts),
        ];
    }

    public function operational(): array
    {
        $attempts = ExamSessionAttempt::query();
        $totalAttempts = (clone $attempts)->count();
        $disrupted = (clone $attempts)->whereIn('status', [ExamSessionAttempt::STATUS_EXPIRED, ExamSessionAttempt::STATUS_TERMINATED])->count();

        return [
            'total_sessions' => ExamSession::query()->count(),
            'attempts_30_days' => (clone $attempts)->where('created_at', '>=', now()->subDays(30))->count(),
            'active_attempts' => (clone $attempts)->whereIn('status', [ExamSessionAttempt::STATUS_IN_PROGRESS, ExamSessionAttempt::STATUS_PAUSED])->count(),
            'disruption_rate' => $totalAttempts > 0 ? round($disrupted / $totalAttempts * 100, 1) : 0,
            'events_24_hours' => ExamProctorEvent::query()->where('occurred_at', '>=', now()->subDay())->count(),
            'camera_snapshots' => ExamProctorEvent::query()->where('type', ExamProctorEvent::TYPE_CAMERA_SNAPSHOT)->count(),
            'recent_sessions' => ExamSession::query()->select(['id', 'title', 'status', 'starts_at', 'ends_at'])->latest()->limit(10)->get(),
        ];
    }

    private function summary(ExamSession $session, Collection $attempts): array
    {
        return [
            'candidates' => $session->candidates->count(),
            'submitted' => $attempts->count(),
            'average_score' => round((float) ($attempts->avg('percentage') ?? 0), 1),
            'pass_rate' => $attempts->isNotEmpty() ? round($attempts->where('passed', true)->count() / $attempts->count() * 100, 1) : 0,
        ];
    }

    private function distribution(Collection $attempts): array
    {
        return collect(['0–19' => [0, 20], '20–39' => [20, 40], '40–59' => [40, 60], '60–79' => [60, 80], '80–100' => [80, 101]])
            ->map(fn (array $range) => $attempts->filter(fn ($attempt) => (float) $attempt->percentage >= $range[0] && (float) $attempt->percentage < $range[1])->count())
            ->all();
    }

    private function questions(ExamSession $session, Collection $answers, int $attemptCount): Collection
    {
        return $session->version->questions->sortBy('sort_order')->values()->map(function ($question) use ($answers, $attemptCount): array {
            $responses = $answers->where('exam_template_question_id', $question->id);
            $answered = $responses->filter(fn ($answer) => filled($answer->answer));
            $correctRate = $answered->isNotEmpty() ? round($answered->where('is_correct', true)->count() / $answered->count() * 100, 1) : 0;
            $blankRate = $attemptCount > 0 ? round(($attemptCount - $answered->count()) / $attemptCount * 100, 1) : 0;
            $choices = $answered->flatMap(fn ($answer) => $answer->answer ?? [])->countBy()->sortDesc()->all();

            return [
                'question' => $question,
                'responses' => $answered->count(),
                'correct_rate' => $correctRate,
                'blank_rate' => $blankRate,
                'average_seconds' => round((float) ($answered->whereNotNull('response_seconds')->avg('response_seconds') ?? 0)),
                'choice_distribution' => $choices,
                'flagged' => $answered->isNotEmpty() && ($correctRate <= 20 || $correctRate >= 95 || $blankRate >= 30),
            ];
        });
    }

    private function supportStudents(Collection $attempts): Collection
    {
        return $attempts->filter(fn ($attempt) => $attempt->passed === false || (float) $attempt->percentage < 50)
            ->sortBy('percentage')->take(20)->values();
    }

    private function classrooms(ExamSession $session, Collection $attempts): Collection
    {
        return $session->assignments->filter(fn ($assignment) => $assignment->assignable instanceof Classroom)
            ->map(function ($assignment) use ($attempts): array {
                $classroomAttempts = $attempts->filter(fn ($attempt) => in_array($assignment->assignable_id, $attempt->candidate?->metadata['classroom_ids'] ?? [], true));

                return [
                    'classroom' => $assignment->assignable,
                    'attempts' => $classroomAttempts->count(),
                    'average_score' => round((float) ($classroomAttempts->avg('percentage') ?? 0), 1),
                    'pass_rate' => $classroomAttempts->isNotEmpty() ? round($classroomAttempts->where('passed', true)->count() / $classroomAttempts->count() * 100, 1) : 0,
                ];
            })->values();
    }
}
