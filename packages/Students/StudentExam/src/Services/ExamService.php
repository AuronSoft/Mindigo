<?php

namespace Mindigo\StudentExam\Services;

use Illuminate\Support\Collection;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;

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
        
        $exams = Exam::query()
        ->where('status', 'published')
        ->where(function ($query) use ($studentId) {
            $query->whereJsonContains('audience->roles', 'student');
        })
        ->with(['attempts' => fn($q) => $q->where('user_id', $studentId)])
        ->orderBy('starts_at')
        ->get();

        $upcoming  = collect();
        $ongoing   = collect();
        $completed = collect();

        $now = now();

        foreach ($exams as $exam) {
            $myAttempts    = $exam->attempts;
            $attemptCount  = $myAttempts->count();
            $maxAttempts   = $exam->max_attempts ?? 1;

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
        if (!$exam->audience) return false;

        $roles = $exam->audience['roles'] ?? [];

        if (in_array('student', $roles)) return true;

        $classrooms = $exam->audience['classrooms'] ?? [];
        if (empty($classrooms)) return false;

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
            ->whereIn('status', ['submitted', 'graded'])
            ->count();

        return $done >= $maxAttempts;
    }

  

    public function startAttempt(Exam $exam, int|string $studentId): ExamAttempt
    {
        // Huỷ attempt in_progress cũ nếu có (hết giờ bỏ ngang)
        ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $studentId)
            ->where('status', 'in_progress')
            ->update(['status' => 'abandoned']);

        $expiresAt = $exam->duration_minutes
            ? now()->addMinutes($exam->duration_minutes)
            : null;

        // Nếu đề có end_time, expires_at không được vượt quá end_time
        if ($expiresAt && $exam->end_time && $expiresAt->gt($exam->end_time)) {
            $expiresAt = $exam->end_time;
        }

        return ExamAttempt::create([
            'exam_id'        => $exam->id,
            'user_id'        => $studentId,
            'started_at'     => now(),
            'expires_at'     => $expiresAt,
            'status'         => 'in_progress',
            'tab_leave_count'=> 0,
        ]);
    }

    

    public function getQuestionsForAttempt(ExamAttempt $attempt): Collection
    {
        $exam = $attempt->exam()->with('questions')->first();

        $questions = $exam->questions;

        return match ($exam->question_order ?? 'fixed') {
            'random' => $questions->shuffle(),
            default  => $questions->sortBy('order_index')->values(),
        };
    }

    public function getSavedAnswers(ExamAttempt $attempt): Collection
    {
        return ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)
        ->get()
        ->keyBy('exam_question_id');
    }

    

    public function submitAttempt(ExamAttempt $attempt, array $validated): ExamAttempt
    {
        $answers        = $validated['answers'] ?? [];
        $tabLeaveCount  = $validated['tab_leave_count'] ?? $attempt->tab_leave_count;

        // Lưu từng đáp án (upsert)
        foreach ($answers as $questionId => $value) {
            ExamAttemptAnswer::updateOrCreate(
            ['exam_attempt_id' => $attempt->id, 'exam_question_id' => $questionId],
            ['answer' => is_array($value) ? json_encode($value) : $value]
        );
        }

        // Chấm điểm tự động
        [$score, $maxScore] = $this->autoGrade($attempt);

        $attempt->update([
            'submitted_at'    => now(),
            'status'          => 'submitted',
            'tab_leave_count' => $tabLeaveCount,
            'score'           => $score,
            'max_score'       => $maxScore,
            'percentage'      => $maxScore > 0 ? round($score / $maxScore * 100, 2) : 0,
            'passed'          => $maxScore > 0 && ($score / $maxScore * 100) >= ($attempt->exam->passing_percentage ?? 50),
        ]);

        return $attempt->fresh();
    }

  
    public function autoSubmit(ExamAttempt $attempt): void
    {
        if (in_array($attempt->status, ['submitted', 'graded'])) {
            return;
        }

        $this->submitAttempt($attempt, ['answers' => [], 'tab_leave_count' => $attempt->tab_leave_count]);
    }

   

    private function autoGrade(ExamAttempt $attempt): array
    {
        $exam = $attempt->exam()->with('questions')->first();
        $questions = $exam->questions;
        $answers = ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)
        ->get()
        ->keyBy('exam_question_id');

        $score    = 0;
        $maxScore = 0;

        foreach ($questions as $question) {
            $point = $question->points ?? 1;
            $maxScore += $point;

            if (!isset($answers[$question->id])) {
                continue;
            }

            $given = $answers[$question->id]->answer;

            // Lấy các option đúng
            $correctOptions = collect($question->correct_answers ?? [])->sort()->values();

        if ($correctOptions->isEmpty()) {
            continue;
        }

            // So sánh đáp án
            $givenIds = is_string($given) && str_starts_with($given, '[')
                ? collect(json_decode($given))->sort()->values()
                : collect([$given])->sort()->values();

            if ($givenIds->toArray() === $correctOptions->toArray()) {
                $score += $point;
            }
        }

        return [$score, $maxScore];
    }

    

    public function getResult(ExamAttempt $attempt): array
    {
        $exam = $attempt->exam()->with('questions.options')->first();

        $answers = ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)->get()->keyBy('exam_question_id');

        // Chỉ show review nếu đề cho phép
        $showReview = (bool) ($exam->show_answers_after_submit ?? false);

        return [
            'exam'       => $exam,
            'score'      => $attempt->score,
            'max_score'  => $attempt->max_score,
            'percentage' => $attempt->percentage,
            'passed'     => $attempt->passed,
            'show_review'=> $showReview,
            'questions'  => $showReview ? $exam->questions : collect(),
            'answers'    => $showReview ? $answers : collect(),
        ];
    }
}