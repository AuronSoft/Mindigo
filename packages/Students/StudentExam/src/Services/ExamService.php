<?php

namespace Mindigo\StudentExam\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamQuestion;

class ExamService
{
    public function classroomIdsForStudent(int|string $studentId): Collection
    {
        return Classroom::query()
            ->whereHas('students', function ($q) use ($studentId) {
                $q->where('student_id', $studentId)
                    ->where('classroom_students.status', 'active');
            })
            ->pluck('id');
    }

    public function getExamsForStudent(int|string $studentId): array
    {
        $classroomIds = $this->classroomIdsForStudent($studentId);

        if ($classroomIds->isEmpty()) {
            return ['upcoming' => collect(), 'ongoing' => collect(), 'completed' => collect()];
        }

        $exams = Exam::query()
            ->where('status', 'published')
            ->whereJsonContains('audience->roles', 'student')
            ->where(function ($query) use ($classroomIds) {
                foreach ($classroomIds as $classroomId) {
                    $query->orWhereJsonContains('audience->classrooms', (int) $classroomId);
                }
            })
            ->with(['attempts' => fn ($q) => $q->where('user_id', $studentId)])
            ->orderBy('starts_at')
            ->get();

        $upcoming = collect();
        $ongoing = collect();
        $completed = collect();

        $now = now();

        foreach ($exams as $exam) {
            $myAttempts = $exam->attempts;
            $attemptCount = $myAttempts->count();
            $maxAttempts = $exam->max_attempts ?? 1;

            if ($exam->starts_at && $now->lt($exam->starts_at)) {
                $upcoming->push($exam);
            } elseif ($exam->ends_at && $now->gt($exam->ends_at)) {
                $completed->push($exam);
            } elseif ($attemptCount >= $maxAttempts) {
                $completed->push($exam);
            } else {
                $ongoing->push($exam);
            }
        }

        return compact('upcoming', 'ongoing', 'completed');
    }

    public function isEnrolledInExamClassroom(Exam $exam, int|string $studentId): bool
    {
        if (! $exam->audience) {
            return false;
        }

        $classrooms = $exam->audience['classrooms'] ?? [];
        if (empty($classrooms)) {
            return false;
        }

        return $this->classroomIdsForStudent($studentId)
            ->intersect($classrooms)
            ->isNotEmpty();
    }

    public function isAvailable(Exam $exam): bool
    {
        if ($exam->status !== 'published') {
            return false;
        }

        $now = now();

        if ($exam->starts_at && $now->lt($exam->starts_at)) {
            return false;
        }

        if ($exam->ends_at && $now->gt($exam->ends_at)) {
            return false;
        }

        return true;
    }

    public function hasExceededAttempts(Exam $exam, int|string $studentId): bool
    {
        $maxAttempts = $exam->max_attempts ?? 1;

        $done = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $studentId)
            ->whereIn('status', ['submitted', 'expired'])
            ->count();

        return $done >= $maxAttempts;
    }

    public function startAttempt(Exam $exam, int|string $studentId): ExamAttempt
    {
        return DB::transaction(function () use ($exam, $studentId): ExamAttempt {
            $activeAttempt = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('user_id', $studentId)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->first();

            if ($activeAttempt && (! $activeAttempt->expires_at || $activeAttempt->expires_at->isFuture())) {
                return $activeAttempt;
            }

            if ($activeAttempt) {
                $this->submitAttempt($activeAttempt, [], 'expired');
            }

            $questionIds = $exam->questions()->orderBy('sort_order')->pluck('id')->all();
            if ($exam->shuffle_questions) {
                shuffle($questionIds);
            }

            $expiresAt = now()->addMinutes($exam->duration_minutes);
            if ($exam->ends_at && $expiresAt->gt($exam->ends_at)) {
                $expiresAt = $exam->ends_at;
            }

            return ExamAttempt::query()->create([
                'exam_id' => $exam->id,
                'user_id' => $studentId,
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'status' => 'in_progress',
                'max_score' => $exam->total_points,
                'question_order' => $questionIds,
                'tab_leave_count' => 0,
            ]);
        });
    }

    public function getQuestionsForAttempt(ExamAttempt $attempt): Collection
    {
        $order = $attempt->question_order ?? [];
        if (empty($order)) {
            $order = $attempt->exam->questions()->orderBy('sort_order')->pluck('id')->all();
            $attempt->forceFill(['question_order' => $order])->save();
        }

        $questions = ExamQuestion::query()->whereIn('id', $order)->get()->keyBy('id');

        return collect($order)->map(fn ($id) => $questions->get($id))->filter()->values();
    }

    public function getSavedAnswers(ExamAttempt $attempt): Collection
    {
        return ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)
            ->get()
            ->keyBy('exam_question_id');
    }

    public function saveAnswer(ExamAttempt $attempt, int $questionId, mixed $answer): void
    {
        $question = ExamQuestion::query()
            ->where('exam_id', $attempt->exam_id)
            ->findOrFail($questionId);

        ExamAttemptAnswer::query()->updateOrCreate(
            ['exam_attempt_id' => $attempt->id, 'exam_question_id' => $question->id],
            ['type' => $question->type, 'answer' => $this->normalizeAnswer($answer)]
        );
    }

    public function submitAttempt(ExamAttempt $attempt, array $validated, string $status = 'submitted'): ExamAttempt
    {
        $answers = $validated['answers'] ?? [];
        $tabLeaveCount = $validated['tab_leave_count'] ?? $attempt->tab_leave_count;

        // Lưu từng đáp án (upsert)
        foreach ($answers as $questionId => $value) {
            ExamAttemptAnswer::updateOrCreate(
                ['exam_attempt_id' => $attempt->id, 'exam_question_id' => $questionId],
                ['type' => $attempt->exam->questions()->findOrFail($questionId)->type, 'answer' => $this->normalizeAnswer($value)]
            );
        }

        // Chấm điểm tự động
        [$score, $maxScore] = $this->autoGrade($attempt);

        $attempt->update([
            'submitted_at' => now(),
            'status' => $status,
            'tab_leave_count' => $tabLeaveCount,
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => $maxScore > 0 ? round($score / $maxScore * 100, 2) : 0,
            'passed' => $score >= (float) $attempt->exam->passing_score,
        ]);

        return $attempt->fresh();
    }

    public function autoSubmit(ExamAttempt $attempt): void
    {
        if (in_array($attempt->status, ['submitted', 'expired'])) {
            return;
        }

        $this->submitAttempt($attempt, ['answers' => [], 'tab_leave_count' => $attempt->tab_leave_count], 'expired');
    }

    private function autoGrade(ExamAttempt $attempt): array
    {
        $exam = $attempt->exam()->with('questions')->first();
        $questions = $exam->questions;
        $answers = ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)
            ->get()
            ->keyBy('exam_question_id');

        $score = 0;
        $maxScore = 0;

        foreach ($questions as $question) {
            $point = $question->points ?? 1;
            $maxScore += $point;

            if (! isset($answers[$question->id])) {
                continue;
            }

            $given = $answers[$question->id]->answer ?? [];

            // Lấy các option đúng
            $correctOptions = collect($question->correct_answers ?? [])->sort()->values();

            if ($correctOptions->isEmpty()) {
                continue;
            }

            // So sánh đáp án
            $givenIds = collect($given)->map(fn ($value) => (string) $value)->sort()->values();
            $correctOptions = $correctOptions->map(fn ($value) => (string) $value)->sort()->values();

            if ($givenIds->toArray() === $correctOptions->toArray()) {
                $score += $point;
            }
        }

        return [$score, $maxScore];
    }

    private function normalizeAnswer(mixed $answer): array
    {
        if (is_string($answer) && str_starts_with(trim($answer), '[')) {
            $decoded = json_decode($answer, true);
            $answer = is_array($decoded) ? $decoded : [$answer];
        }

        return collect(is_array($answer) ? $answer : [$answer])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (string) $value)
            ->values()
            ->all();
    }

    public function getResult(ExamAttempt $attempt): array
    {
        $exam = $attempt->exam()->with('questions.options')->first();

        $answers = ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)->get()->keyBy('exam_question_id');

        // Chỉ show review nếu đề cho phép
        $showReview = (bool) ($exam->show_answers_after_submit ?? false);

        return [
            'exam' => $exam,
            'score' => $attempt->score,
            'max_score' => $attempt->max_score,
            'percentage' => $attempt->percentage,
            'passed' => $attempt->passed,
            'show_review' => $showReview,
            'questions' => $showReview ? $exam->questions : collect(),
            'answers' => $showReview ? $answers : collect(),
        ];
    }
}
