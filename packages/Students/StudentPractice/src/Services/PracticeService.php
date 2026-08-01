<?php

namespace Mindigo\StudentPractice\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\QuestionBank\Services\QuestionBankService;
use Mindigo\StudentPractice\Contracts\PracticeServiceInterface;
use Mindigo\StudentPractice\Models\PracticeAnswer;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeSet;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\StudentPractice\Models\StudentSkillProgress;

class PracticeService implements PracticeServiceInterface
{
    public function __construct(
        protected QuestionBankService $questionBank,
        private readonly PracticeSkillService $skills,
        private readonly SkillQuestionSelector $skillSelector,
        private readonly SkillProgressService $skillProgress,
        private readonly PracticeRecommendationService $recommendations,
    ) {}

    public function getQuestions(array $filters): LengthAwarePaginator
    {
        return $this->approvedQuestions($filters)
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();
    }

    public function getQuestion(int $id): ?Question
    {
        return Question::query()->practiceReady()->find($id);
    }

    public function formData(User $user): array
    {
        return [
            'types' => Question::TYPES,
            'difficulties' => Question::DIFFICULTIES,
            'subjects' => $this->questionBank->subjects(),
            'subjectTopics' => $this->questionBank->topicsBySubject(),
            'skills' => $this->skills->activeCatalog(),
        ];
    }

    public function startPractice(User $student, array $data): PracticeAttempt
    {
        $questionCount = (int) ($data['question_count'] ?? 10);
        $questions = $this->approvedQuestions($data)
            ->inRandomOrder()
            ->limit($questionCount)
            ->get();

        return $this->createAttempt($student, $questions, $data);
    }

    public function startSkillPractice(User $student, PracticeSkill $skill, array $data): PracticeAttempt
    {
        [$questions, $poolSize] = $this->skillSelector->select(
            $student,
            $skill,
            (int) $data['question_count'],
            $data['difficulty'] ?? null,
        );

        return $this->createAttempt($student, $questions, [
            'skill_id' => $skill->getKey(),
            'mode' => PracticeAttempt::MODE_SKILL,
            'subject' => $skill->subject?->name,
            'topic' => $skill->topic?->name,
            'difficulty' => $data['difficulty'] ?? null,
            'question_count' => (int) $data['question_count'],
            'question_pool_size' => $poolSize,
            'selection_strategy' => 'balanced',
        ]);
    }

    public function startAdaptivePractice(User $student, PracticeSkill $skill, int $questionCount): PracticeAttempt
    {
        $progress = StudentSkillProgress::query()->where('student_id', $student->getAuthIdentifier())
            ->where('practice_skill_id', $skill->getKey())->first();
        $difficulty = $progress?->recommended_difficulty ?? MasteryCalculator::DIFFICULTY_EASY;
        [$questions, $poolSize] = $this->skillSelector->selectAdaptive($student, $skill, $questionCount, $difficulty);

        return $this->createAttempt($student, $questions, [
            'skill_id' => $skill->getKey(),
            'mode' => PracticeAttempt::MODE_SKILL,
            'subject' => $skill->subject?->name,
            'topic' => $skill->topic?->name,
            'difficulty' => $difficulty,
            'question_count' => $questionCount,
            'question_pool_size' => $poolSize,
            'selection_strategy' => 'adaptive_v1',
            'is_adaptive' => true,
            'mastery_before' => $progress?->mastery_score ?? 0,
            'adaptive_context' => [
                'target_difficulty' => $difficulty,
                'confidence_score' => $progress?->confidence_score ?? 0,
                'engine_version' => MasteryCalculator::VERSION,
            ],
        ]);
    }

    public function startPracticeSet(User $student, PracticeSet $set): PracticeAttempt
    {
        $set->loadMissing('questions');

        return $this->createAttempt($student, $set->questions, [
            'practice_set_id' => $set->getKey(),
            'mode' => $set->topic
                ? PracticeAttempt::MODE_TOPIC
                : ($set->subject ? PracticeAttempt::MODE_SUBJECT : PracticeAttempt::MODE_MIXED),
            'subject' => $set->subject,
            'topic' => $set->topic,
            'difficulty' => $set->difficulty,
            'question_count' => $set->questions->count(),
        ]);
    }

    public function submitAnswer(PracticeAttempt $attempt, int $questionId, array $answer): PracticeAnswer
    {
        $saved = DB::transaction(function () use ($attempt, $questionId, $answer): ?PracticeAnswer {
            $lockedAttempt = PracticeAttempt::query()->lockForUpdate()->findOrFail($attempt->getKey());
            if ($lockedAttempt->isExpired()) {
                $this->markExpired($lockedAttempt);

                return null;
            }
            if ($lockedAttempt->isCompleted()) {
                throw ValidationException::withMessages([
                    'attempt' => __('student-practice::app.errors.already_completed'),
                ]);
            }

            $practiceAnswer = PracticeAnswer::query()
                ->where('attempt_id', $lockedAttempt->getKey())
                ->where('question_id', $questionId)
                ->lockForUpdate()
                ->first();

            if (! $practiceAnswer) {
                throw ValidationException::withMessages([
                    'question_id' => __('student-practice::app.errors.question_not_in_attempt'),
                ]);
            }

            $snapshot = $practiceAnswer->question_snapshot ?? [];
            if (! array_key_exists('correct_answers', $snapshot)) {
                $question = Question::query()->find($questionId);
                $snapshot = $question ? $this->questionSnapshot($question) : [];
            }
            if ($snapshot === []) {
                throw ValidationException::withMessages([
                    'question_id' => __('student-practice::app.errors.question_unavailable'),
                ]);
            }

            $this->validateAnswerShape((string) data_get($snapshot, 'type'), $answer);
            if ($practiceAnswer->student_answer === $answer) {
                $lockedAttempt->update(['last_activity_at' => now()]);

                return $practiceAnswer->fresh('question');
            }

            $isCorrect = $this->gradeAnswer($snapshot, $answer);
            $practiceAnswer->update([
                'student_answer' => $answer,
                'is_correct' => $isCorrect,
                'points' => $isCorrect ? 1 : 0,
                'response_seconds' => min(65535, max(0, now()->diffInSeconds($lockedAttempt->last_activity_at))),
                'answer_revision' => $practiceAnswer->answer_revision + 1,
                'answered_at' => now(),
            ]);
            $lockedAttempt->update(['last_activity_at' => now()]);

            return $practiceAnswer->fresh('question');
        });

        if ($saved === null) {
            throw ValidationException::withMessages([
                'attempt' => __('student-practice::app.errors.session_expired'),
            ]);
        }

        return $saved;
    }

    public function completePractice(PracticeAttempt $attempt): PracticeAttempt
    {
        $completed = DB::transaction(function () use ($attempt): PracticeAttempt {
            $lockedAttempt = PracticeAttempt::query()->lockForUpdate()->findOrFail($attempt->getKey());
            if ($lockedAttempt->isCompleted()) {
                return $lockedAttempt;
            }
            if ($lockedAttempt->isExpired()) {
                $this->markExpired($lockedAttempt);

                return $lockedAttempt->fresh();
            }

            $correctAnswers = $lockedAttempt->answers()->where('is_correct', true)->count();
            $score = $lockedAttempt->total_questions > 0
                ? round(($correctAnswers / $lockedAttempt->total_questions) * 100, 2)
                : 0;

            $lockedAttempt->update([
                'correct_answers' => $correctAnswers,
                'score' => $score,
                'status' => PracticeAttempt::STATUS_COMPLETED,
                'last_activity_at' => now(),
                'completed_at' => now(),
            ]);

            if ($lockedAttempt->practice_skill_id !== null) {
                User::query()->whereKey($lockedAttempt->student_id)->lockForUpdate()->firstOrFail();
                $lockedAttempt->loadMissing(['student', 'practiceSkill']);
                $progress = $this->skillProgress->rebuild($lockedAttempt->student, $lockedAttempt->practiceSkill);
                $this->recommendations->refresh($progress);
                if ($lockedAttempt->is_adaptive) {
                    $lockedAttempt->update(['mastery_after' => $progress->mastery_score]);
                }
            }

            return $lockedAttempt->fresh();
        });

        if ($completed->status === PracticeAttempt::STATUS_EXPIRED) {
            throw ValidationException::withMessages([
                'attempt' => __('student-practice::app.errors.session_expired'),
            ]);
        }

        return $completed;
    }

    public function getStudentHistory(User $student): LengthAwarePaginator
    {
        return PracticeAttempt::query()
            ->with(['practiceSet:id,title', 'practiceSkill:id,name'])
            ->where('student_id', $student->getAuthIdentifier())
            ->where('status', PracticeAttempt::STATUS_COMPLETED)
            ->latest('completed_at')
            ->paginate((int) config('practice.session.history_per_page'))
            ->withQueryString();
    }

    public function getStudentStats(User $student): array
    {
        $summary = PracticeAttempt::query()
            ->where('student_id', $student->getAuthIdentifier())
            ->where('status', PracticeAttempt::STATUS_COMPLETED)
            ->selectRaw('COUNT(*) as total_attempts')
            ->selectRaw('COALESCE(SUM(total_questions), 0) as total_questions')
            ->selectRaw('COALESCE(SUM(correct_answers), 0) as total_correct')
            ->selectRaw('COALESCE(AVG(score), 0) as average_score')
            ->first();

        $totalQuestions = (int) $summary->total_questions;
        $totalCorrect = (int) $summary->total_correct;

        return [
            'total_attempts' => (int) $summary->total_attempts,
            'total_questions' => $totalQuestions,
            'total_correct' => $totalCorrect,
            'average_score' => round((float) $summary->average_score, 2),
            'completion_rate' => $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 2) : 0,
        ];
    }

    public function getPracticeDetails(PracticeAttempt $attempt): array
    {
        $attempt->loadMissing(['answers.question', 'practiceSet:id,title']);

        return [
            'attempt' => $attempt,
            'answers' => $attempt->answers,
            'summary' => [
                'total_questions' => $attempt->total_questions,
                'correct_answers' => $attempt->correct_answers,
                'score' => $attempt->score,
                'duration' => $attempt->duration_in_minutes,
            ],
        ];
    }

    public function reconcileAttempt(PracticeAttempt $attempt): PracticeAttempt
    {
        return DB::transaction(function () use ($attempt): PracticeAttempt {
            $lockedAttempt = PracticeAttempt::query()->lockForUpdate()->findOrFail($attempt->getKey());
            if ($lockedAttempt->isExpired()) {
                $this->markExpired($lockedAttempt);
            }

            return $lockedAttempt->fresh();
        });
    }

    private function approvedQuestions(array $filters)
    {
        $query = Question::query()->practiceReady();

        foreach (['subject', 'topic', 'type', 'difficulty'] as $filter) {
            if (filled($filters[$filter] ?? null)) {
                $query->where($filter, $filters[$filter]);
            }
        }

        if (filled($filters['skill_id'] ?? null)) {
            $query->whereIn('id', DB::table('question_practice_skill')
                ->select('question_id')
                ->where('practice_skill_id', (int) $filters['skill_id']));
        }

        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('content', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%")
                    ->orWhere('topic', 'like', "%{$keyword}%");
            });
        }

        return $query;
    }

    private function createAttempt(User $student, Collection $questions, array $data): PracticeAttempt
    {
        if ($questions->isEmpty()) {
            throw ValidationException::withMessages([
                'question_count' => __('student-practice::app.errors.no_questions'),
            ]);
        }

        return DB::transaction(function () use ($student, $questions, $data): PracticeAttempt {
            User::query()->whereKey($student->getAuthIdentifier())->lockForUpdate()->firstOrFail();
            $fingerprint = $this->requestFingerprint($data);
            $activeAttempt = PracticeAttempt::query()
                ->where('student_id', $student->getAuthIdentifier())
                ->where('status', PracticeAttempt::STATUS_IN_PROGRESS)
                ->where('request_fingerprint', $fingerprint)
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->latest('id')->first();
            if ($activeAttempt) {
                return $activeAttempt->load('answers.question');
            }

            $now = now();
            $attempt = PracticeAttempt::query()->create([
                'student_id' => $student->getAuthIdentifier(),
                'practice_set_id' => $data['practice_set_id'] ?? null,
                'practice_skill_id' => $data['skill_id'] ?? null,
                'mode' => $data['mode'] ?? PracticeAttempt::MODE_MIXED,
                'subject' => $data['subject'] ?? null,
                'topic' => $data['topic'] ?? null,
                'difficulty' => $data['difficulty'] ?? null,
                'question_pool_size' => $data['question_pool_size'] ?? $questions->count(),
                'selection_strategy' => $data['selection_strategy'] ?? 'random',
                'is_adaptive' => $data['is_adaptive'] ?? false,
                'mastery_before' => $data['mastery_before'] ?? null,
                'adaptive_context' => $data['adaptive_context'] ?? null,
                'request_fingerprint' => $fingerprint,
                'total_questions' => $questions->count(),
                'correct_answers' => 0,
                'status' => PracticeAttempt::STATUS_IN_PROGRESS,
                'started_at' => $now,
                'last_activity_at' => $now,
                'expires_at' => $now->copy()->addMinutes((int) config('practice.session.ttl_minutes')),
            ]);

            $attempt->answers()->createMany($questions->values()->map(fn (Question $question): array => [
                'question_id' => $question->getKey(),
                'question_snapshot' => $this->questionSnapshot($question),
                'difficulty_snapshot' => $question->difficulty,
                'student_answer' => null,
                'is_correct' => false,
                'points' => 0,
            ])->all());

            return $attempt->load('answers.question');
        });
    }

    private function gradeAnswer(array $snapshot, array $answer): bool
    {
        $correctAnswers = data_get($snapshot, 'correct_answers', []);

        return match (data_get($snapshot, 'type')) {
            'single_choice' => isset($answer['choice'], $correctAnswers[0])
                && (string) $answer['choice'] === (string) $correctAnswers[0],
            'multiple_choice' => $this->matchesMultipleChoice($answer['choices'] ?? null, $correctAnswers),
            'true_false' => array_key_exists('answer', $answer) && isset($correctAnswers[0])
                && filter_var($answer['answer'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
                    === filter_var($correctAnswers[0], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE),
            'short_answer' => $this->matchShortAnswer((string) ($answer['text'] ?? ''), $correctAnswers),
            default => false,
        };
    }

    private function validateAnswerShape(string $type, array $answer): void
    {
        $valid = match ($type) {
            'single_choice' => filled($answer['choice'] ?? null),
            'multiple_choice' => is_array($answer['choices'] ?? null) && $answer['choices'] !== [],
            'true_false' => array_key_exists('answer', $answer),
            'short_answer', 'essay' => filled($answer['text'] ?? null),
            default => false,
        };
        if (! $valid) {
            throw ValidationException::withMessages([
                'answer' => __('student-practice::app.validation.answer_invalid'),
            ]);
        }
    }

    private function questionSnapshot(Question $question): array
    {
        return [
            'content' => $question->content,
            'type' => $question->type,
            'options' => $question->options,
            'correct_answers' => $question->correct_answers,
            'explanation' => $question->explanation,
            'hint' => $question->hint,
        ];
    }

    private function requestFingerprint(array $data): string
    {
        $identity = collect($data)->only([
            'practice_set_id', 'skill_id', 'mode', 'subject', 'topic', 'difficulty',
            'question_count', 'selection_strategy', 'is_adaptive',
        ])->sortKeys()->all();

        return hash('sha256', json_encode($identity, JSON_THROW_ON_ERROR));
    }

    private function markExpired(PracticeAttempt $attempt): void
    {
        if ($attempt->status !== PracticeAttempt::STATUS_IN_PROGRESS) {
            return;
        }
        $attempt->update([
            'status' => PracticeAttempt::STATUS_EXPIRED,
            'last_activity_at' => now(),
            'completed_at' => now(),
        ]);
    }

    private function matchesMultipleChoice(mixed $answers, array $correctAnswers): bool
    {
        if (! is_array($answers)) {
            return false;
        }

        $submitted = array_values(array_unique(array_map('strval', $answers)));
        $correct = array_values(array_unique(array_map('strval', $correctAnswers)));
        sort($submitted);
        sort($correct);

        return $submitted === $correct;
    }

    private function matchShortAnswer(string $studentAnswer, array $correctAnswers): bool
    {
        $normalized = mb_strtolower(trim($studentAnswer));

        return collect($correctAnswers)->contains(
            fn (mixed $correct): bool => mb_strtolower(trim((string) $correct)) === $normalized
        );
    }
}
