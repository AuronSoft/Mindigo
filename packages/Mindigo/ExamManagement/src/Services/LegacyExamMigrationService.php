<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAssignment;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamMigrationRun;
use Mindigo\ExamManagement\Models\ExamProctorEvent;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Mindigo\TeacherClassroom\Models\Classroom;
use Throwable;

class LegacyExamMigrationService
{
    public function preview(array $examIds = []): array
    {
        $exams = $this->sourceQuery($examIds)->get();
        $issues = [];
        foreach ($exams as $exam) {
            if (! $exam->created_by || ! User::query()->whereKey($exam->created_by)->exists()) {
                $issues[] = ['exam_id' => $exam->id, 'reason' => 'missing_owner'];
            }
            if ($exam->questions->isEmpty()) {
                $issues[] = ['exam_id' => $exam->id, 'reason' => 'no_questions'];
            }
        }

        return [
            'selected_exams' => $exams->count(),
            'questions' => $exams->sum(fn (Exam $exam): int => $exam->questions->count()),
            'attempts' => $exams->sum(fn (Exam $exam): int => $exam->attempts->count()),
            'answers' => $exams->sum(fn (Exam $exam): int => $exam->attempts->sum(fn ($attempt): int => $attempt->answers->count())),
            'already_migrated' => ExamTemplate::query()->whereNotNull('legacy_exam_id')->when($examIds, fn ($query) => $query->whereIn('legacy_exam_id', $examIds))->count(),
            'issues' => $issues,
        ];
    }

    public function migrate(array $examIds = [], ?int $initiatedBy = null): ExamMigrationRun
    {
        $run = ExamMigrationRun::query()->create([
            'uuid' => (string) Str::uuid(),
            'mode' => 'migrate',
            'status' => 'running',
            'legacy_exam_ids' => $examIds ?: null,
            'initiated_by' => $initiatedBy,
            'started_at' => now(),
        ]);
        $summary = ['selected' => 0, 'migrated' => 0, 'skipped' => 0, 'failed' => 0];
        $issues = [];

        $this->sourceQuery($examIds)->chunkById(100, function ($exams) use (&$summary, &$issues): void {
            foreach ($exams as $exam) {
                $summary['selected']++;
                if (ExamTemplate::query()->where('legacy_exam_id', $exam->id)->exists()) {
                    $summary['skipped']++;

                    continue;
                }

                try {
                    DB::transaction(fn () => $this->migrateExam($exam));
                    $summary['migrated']++;
                } catch (Throwable $exception) {
                    report($exception);
                    $summary['failed']++;
                    $issues[] = ['exam_id' => $exam->id, 'reason' => $exception->getMessage()];
                }
            }
        });

        $comparison = $this->compare($examIds);
        $run->update([
            'status' => $summary['failed'] > 0 ? 'completed_with_issues' : 'completed',
            'summary' => array_merge($summary, ['comparison' => $comparison]),
            'issues' => $issues,
            'completed_at' => now(),
        ]);

        return $run->fresh();
    }

    public function compare(array $examIds = []): array
    {
        $legacyExams = Exam::query()->when($examIds, fn ($query) => $query->whereIn('id', $examIds));
        $legacyQuestions = DB::table('exam_questions')->when($examIds, fn ($query) => $query->whereIn('exam_id', $examIds));
        $legacyAttempts = DB::table('exam_attempts')->when($examIds, fn ($query) => $query->whereIn('exam_id', $examIds));
        $legacyAnswers = DB::table('exam_attempt_answers')->when($examIds, fn ($query) => $query->whereIn('exam_attempt_id', (clone $legacyAttempts)->select('id')));

        return [
            'exams' => $this->pair($legacyExams->count(), ExamTemplate::query()->whereNotNull('legacy_exam_id')->when($examIds, fn ($query) => $query->whereIn('legacy_exam_id', $examIds))->count()),
            'sessions' => $this->pair($legacyExams->count(), ExamSession::query()->whereNotNull('legacy_exam_id')->when($examIds, fn ($query) => $query->whereIn('legacy_exam_id', $examIds))->count()),
            'questions' => $this->pair($legacyQuestions->count(), ExamTemplateQuestion::query()->whereNotNull('legacy_exam_question_id')->when($examIds, fn ($query) => $query->whereIn('legacy_exam_question_id', $legacyQuestions->select('id')))->count()),
            'attempts' => $this->pair($legacyAttempts->count(), ExamSessionAttempt::query()->whereNotNull('legacy_exam_attempt_id')->when($examIds, fn ($query) => $query->whereIn('legacy_exam_attempt_id', $legacyAttempts->select('id')))->count()),
            'answers' => $this->pair($legacyAnswers->count(), ExamSessionAttemptAnswer::query()->whereNotNull('legacy_exam_attempt_answer_id')->when($examIds, fn ($query) => $query->whereIn('legacy_exam_attempt_answer_id', $legacyAnswers->select('id')))->count()),
        ];
    }

    public function rollback(array $examIds): array
    {
        $templates = ExamTemplate::query()->whereIn('legacy_exam_id', $examIds)->get();
        $summary = ['selected' => count($examIds), 'removed' => 0];
        foreach ($templates as $template) {
            DB::transaction(function () use ($template, &$summary): void {
                $template->sessions()->withTrashed()->get()->each->forceDelete();
                $template->versions()->delete();
                $template->forceDelete();
                $summary['removed']++;
            });
        }

        return $summary;
    }

    private function migrateExam(Exam $exam): void
    {
        $exam->loadMissing(['questions', 'attempts.answers', 'attempts.user']);
        $owner = User::query()->findOrFail($exam->created_by);
        $template = ExamTemplate::query()->create([
            'legacy_exam_id' => $exam->id,
            'owner_id' => $owner->id,
            'title' => $exam->title,
            'slug' => $this->slug('legacy-template', $exam->slug, $exam->id),
            'subject' => $exam->subject,
            'topic' => $exam->topic,
            'status' => $exam->status === 'draft' ? ExamTemplate::STATUS_DRAFT : ExamTemplate::STATUS_READY,
            'description' => $exam->description,
            'current_version' => 1,
            'total_questions' => $exam->total_questions ?: $exam->questions->count(),
            'total_points' => $exam->total_points,
            'ready_at' => $exam->status === 'draft' ? null : ($exam->published_at ?? $exam->updated_at),
        ]);
        $version = ExamTemplateVersion::query()->create([
            'exam_template_id' => $template->id,
            'created_by' => $owner->id,
            'version' => 1,
            'title' => $exam->title,
            'description' => $exam->description,
            'settings' => ['legacy_exam_id' => $exam->id, 'generation_config' => $exam->generation_config],
            'total_questions' => $template->total_questions,
            'total_points' => $exam->total_points,
            'locked_at' => $exam->status === 'draft' ? null : ($exam->published_at ?? $exam->updated_at),
        ]);
        $questionMap = [];
        foreach ($exam->questions as $question) {
            $snapshot = ExamTemplateQuestion::query()->create([
                'legacy_exam_question_id' => $question->id,
                'exam_template_version_id' => $version->id,
                'source_question_id' => $question->question_id,
                'sort_order' => $question->sort_order,
                'type' => $question->type,
                'difficulty' => $question->difficulty,
                'content' => $question->content,
                'options' => $question->options,
                'correct_answers' => $question->correct_answers,
                'explanation' => $question->explanation,
                'points' => $question->points,
            ]);
            $questionMap[$question->id] = $snapshot->id;
        }

        $session = ExamSession::query()->create([
            'legacy_exam_id' => $exam->id,
            'exam_template_version_id' => $version->id,
            'organizer_id' => $owner->id,
            'title' => $exam->title,
            'slug' => $this->slug('legacy-session', $exam->slug, $exam->id),
            'status' => $this->sessionStatus($exam),
            'starts_at' => $exam->starts_at,
            'ends_at' => $exam->ends_at,
            'duration_minutes' => $exam->duration_minutes,
            'max_attempts' => $exam->max_attempts,
            'passing_score' => $exam->passing_score,
            'shuffle_questions' => $exam->shuffle_questions,
            'shuffle_answers' => $exam->shuffle_answers,
            'result_policy' => $exam->show_results ? 'immediate' : 'after_release',
            'security_policy' => ['legacy_tab_monitoring' => true],
            'scheduled_at' => $exam->published_at,
            'completed_at' => $exam->status === 'closed' ? ($exam->ends_at ?? $exam->updated_at) : null,
        ]);
        $classroomIds = Classroom::query()->whereIn('id', array_map('intval', $exam->audience['classrooms'] ?? []))->pluck('id')->all();
        foreach ($classroomIds as $classroomId) {
            ExamAssignment::query()->create(['exam_session_id' => $session->id, 'assignable_type' => Classroom::class, 'assignable_id' => $classroomId, 'assigned_by' => $owner->id, 'assigned_at' => $exam->published_at ?? $exam->created_at]);
        }

        $audienceUserIds = DB::table('classroom_students')->whereIn('classroom_id', $classroomIds)->where('status', 'active')->pluck('student_id');
        $userIds = $audienceUserIds->merge($exam->attempts->pluck('user_id'))->unique()->values();
        $users = User::query()->whereIn('id', $userIds)->get()->keyBy('id');
        foreach ($users as $user) {
            $candidateClassrooms = DB::table('classroom_students')->whereIn('classroom_id', $classroomIds)->where('student_id', $user->id)->pluck('classroom_id')->map(fn ($id): int => (int) $id)->all();
            ExamCandidate::query()->create(['exam_session_id' => $session->id, 'user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'status' => ExamCandidate::STATUS_ELIGIBLE, 'metadata' => ['classroom_ids' => $candidateClassrooms, 'legacy' => true]]);
        }

        foreach ($exam->attempts->groupBy('user_id') as $legacyAttempts) {
            foreach ($legacyAttempts->sortBy('id')->values() as $index => $legacyAttempt) {
                $candidate = ExamCandidate::query()->where('exam_session_id', $session->id)->where('user_id', $legacyAttempt->user_id)->firstOrFail();
                $needsReview = $legacyAttempt->answers->contains('needs_review', true);
                $attempt = ExamSessionAttempt::query()->create([
                    'legacy_exam_attempt_id' => $legacyAttempt->id,
                    'exam_session_id' => $session->id,
                    'exam_candidate_id' => $candidate->id,
                    'user_id' => $legacyAttempt->user_id,
                    'attempt_number' => $index + 1,
                    'status' => $legacyAttempt->status,
                    'started_at' => $legacyAttempt->started_at,
                    'expires_at' => $legacyAttempt->expires_at ?? $legacyAttempt->started_at->copy()->addMinutes($exam->duration_minutes),
                    'last_activity_at' => $legacyAttempt->last_activity_at ?? $legacyAttempt->submitted_at ?? $legacyAttempt->updated_at,
                    'submitted_at' => $legacyAttempt->submitted_at,
                    'question_order' => collect($legacyAttempt->question_order)->map(fn ($id) => $questionMap[$id] ?? null)->filter()->values()->all(),
                    'security_events' => ['legacy_tab_leave_count' => $legacyAttempt->tab_leave_count],
                    'score' => $legacyAttempt->score,
                    'max_score' => $legacyAttempt->max_score,
                    'percentage' => $legacyAttempt->percentage,
                    'passed' => $legacyAttempt->passed,
                    'needs_review' => $needsReview,
                    'grading_status' => $needsReview ? ExamSessionAttempt::GRADING_PENDING_MANUAL : ($legacyAttempt->submitted_at ? ExamSessionAttempt::GRADING_COMPLETED : ExamSessionAttempt::GRADING_AUTO_GRADED),
                    'reviewed_by' => $legacyAttempt->graded_by,
                    'reviewed_at' => $legacyAttempt->graded_at,
                    'released_at' => $exam->show_results && $legacyAttempt->submitted_at ? $legacyAttempt->submitted_at : null,
                ]);
                foreach ($legacyAttempt->answers as $answer) {
                    if (! isset($questionMap[$answer->exam_question_id])) {
                        continue;
                    }
                    ExamSessionAttemptAnswer::query()->create([
                        'legacy_exam_attempt_answer_id' => $answer->id,
                        'exam_session_attempt_id' => $attempt->id,
                        'exam_template_question_id' => $questionMap[$answer->exam_question_id],
                        'type' => $answer->type,
                        'answer' => $answer->answer,
                        'is_correct' => $answer->is_correct,
                        'points_awarded' => $answer->points_awarded,
                        'needs_review' => $answer->needs_review,
                        'feedback' => $answer->feedback,
                        'reviewed_by' => $answer->graded_by,
                        'reviewed_at' => $answer->graded_at,
                    ]);
                }
                if ($legacyAttempt->tab_leave_count > 0) {
                    ExamProctorEvent::query()->create(['exam_session_id' => $session->id, 'exam_session_attempt_id' => $attempt->id, 'type' => ExamProctorEvent::TYPE_TAB_HIDDEN, 'source' => ExamProctorEvent::SOURCE_CLIENT, 'risk_level' => ExamProctorEvent::RISK_LOW, 'metadata' => ['legacy_count' => $legacyAttempt->tab_leave_count], 'occurred_at' => $legacyAttempt->updated_at]);
                }
            }
        }
    }

    private function sourceQuery(array $examIds)
    {
        return Exam::query()->with(['questions', 'attempts.answers'])->when($examIds, fn ($query) => $query->whereIn('id', $examIds))->orderBy('id');
    }

    private function pair(int $legacy, int $migrated): array
    {
        return ['legacy' => $legacy, 'migrated' => $migrated, 'matches' => $legacy === $migrated];
    }

    private function sessionStatus(Exam $exam): string
    {
        if ($exam->status === 'closed') {
            return ExamSession::STATUS_COMPLETED;
        }
        if ($exam->status === 'draft') {
            return ExamSession::STATUS_DRAFT;
        }
        if ($exam->ends_at?->isPast()) {
            return ExamSession::STATUS_ENDED;
        }
        if ($exam->starts_at?->isPast()) {
            return ExamSession::STATUS_LIVE;
        }

        return ExamSession::STATUS_SCHEDULED;
    }

    private function slug(string $prefix, string $slug, int $id): string
    {
        return Str::limit($prefix.'-'.($slug ?: $id), 230, '').'-'.$id;
    }
}
