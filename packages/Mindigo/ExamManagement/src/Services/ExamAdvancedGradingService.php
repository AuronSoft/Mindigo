<?php

namespace Mindigo\ExamManagement\Services;

use Barryvdh\DomPDF\PDF;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamGradeAppeal;
use Mindigo\ExamManagement\Models\ExamGradingAssignment;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;

class ExamAdvancedGradingService
{
    public function __construct(private readonly ExamGradingService $grading) {}

    public function assign(ExamSession $session, User $organizer, User $grader): void
    {
        $this->ensureOrganizer($session, $organizer);
        if (! $grader->isTeacher() || ! $grader->is_active) {
            throw ValidationException::withMessages(['grader_id' => __('Mindigo-exam-management::app.grading.invalid_grader')]);
        }
        ExamGradingAssignment::query()->updateOrCreate(
            ['exam_session_id' => $session->id, 'grader_id' => $grader->id],
            ['assigned_by' => $organizer->id],
        );
    }

    public function availableGraders(ExamSession $session, User $organizer): Collection
    {
        $this->ensureOrganizer($session, $organizer);

        return User::query()->where('role', 'teacher')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']);
    }

    public function questionWorkspace(ExamSession $session, ExamTemplateQuestion $question, User $grader): Collection
    {
        $this->grading->ensureGrader($session, $grader);
        abort_unless((int) $question->exam_template_version_id === (int) $session->exam_template_version_id, 404);

        return ExamSessionAttemptAnswer::query()
            ->where('exam_template_question_id', $question->id)
            ->whereHas('attempt', fn ($query) => $query->where('exam_session_id', $session->id)->whereIn('status', [ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED, ExamSessionAttempt::STATUS_TERMINATED]))
            ->with(['attempt.candidate', 'attempt.user', 'question', 'revisions.editor'])->orderBy('id')->get();
    }

    public function regrade(ExamSession $session, User $grader): int
    {
        $this->grading->ensureGrader($session, $grader);
        $changed = 0;
        $session->attempts()->whereIn('status', [ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED, ExamSessionAttempt::STATUS_TERMINATED])
            ->with(['answers.question', 'session'])->chunkById(100, function ($attempts) use ($grader, &$changed): void {
                foreach ($attempts as $attempt) {
                    foreach ($attempt->answers as $answer) {
                        if ($answer->question->type === 'essay') {
                            continue;
                        }
                        $given = collect($answer->answer ?? [])->map(fn ($value) => mb_strtolower(trim((string) $value)))->sort()->values()->all();
                        $correct = collect($answer->question->correct_answers ?? [])->map(fn ($value) => mb_strtolower(trim((string) $value)))->sort()->values()->all();
                        $points = $correct !== [] && $given === $correct ? (float) $answer->question->points : 0;
                        if ((float) $answer->points_awarded !== $points) {
                            $this->grading->grade($attempt, $answer, $grader, $points, $answer->feedback, [], 'bulk_regrade');
                            $changed++;
                        }
                    }
                }
            });

        return $changed;
    }

    public function appeal(ExamSessionAttempt $attempt, User $student, string $reason): ExamGradeAppeal
    {
        if ((int) $attempt->user_id !== (int) $student->id || ! $attempt->released_at) {
            throw new AuthorizationException;
        }

        return ExamGradeAppeal::query()->firstOrCreate(
            ['exam_session_attempt_id' => $attempt->id, 'requested_by' => $student->id],
            ['reason' => $reason],
        );
    }

    public function resolve(ExamGradeAppeal $appeal, User $grader, string $status, string $resolution): void
    {
        $appeal->loadMissing('attempt.session');
        $this->grading->ensureGrader($appeal->attempt->session, $grader);
        if ($appeal->status !== ExamGradeAppeal::STATUS_OPEN) {
            throw ValidationException::withMessages(['appeal' => __('Mindigo-exam-management::app.grading.appeal_resolved')]);
        }
        $appeal->update(['status' => $status, 'resolution' => $resolution, 'resolved_by' => $grader->id, 'resolved_at' => now()]);
    }

    public function spreadsheet(ExamSession $session, User $grader): Response
    {
        $this->grading->ensureGrader($session, $grader);
        $rows = $this->exportRows($session);
        $xmlRows = $rows->map(fn (array $row): string => '<Row>'.collect($row)->map(fn ($value): string => '<Cell><Data ss:Type="String">'.e((string) $value).'</Data></Cell>')->join('').'</Row>')->join('');
        $xml = '<?xml version="1.0"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Results"><Table>'.$xmlRows.'</Table></Worksheet></Workbook>';

        return response($xml, 200, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="exam-results-'.$session->id.'.xls"']);
    }

    public function pdf(ExamSession $session, User $grader): PDF
    {
        $this->grading->ensureGrader($session, $grader);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('Mindigo-exam-management::grading.export-pdf', ['session' => $session, 'rows' => $this->exportRows($session)]);
    }

    private function exportRows(ExamSession $session): Collection
    {
        $attempts = $session->attempts()->whereIn('status', [ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED, ExamSessionAttempt::STATUS_TERMINATED])->with(['candidate', 'user'])->get();

        return collect([['Candidate', 'Email', 'Score', 'Maximum', 'Percentage', 'Status']])->concat($attempts->map(fn (ExamSessionAttempt $attempt): array => [
            $session->anonymous_grading ? $attempt->anonymous_code : ($attempt->candidate?->name ?? $attempt->user?->name),
            $session->anonymous_grading ? '' : ($attempt->candidate?->email ?? $attempt->user?->email),
            $attempt->score, $attempt->max_score, $attempt->percentage.'%', $attempt->grading_status,
        ]));
    }

    private function ensureOrganizer(ExamSession $session, User $teacher): void
    {
        if (! $teacher->isTeacher() || (int) $session->organizer_id !== (int) $teacher->id) {
            throw new AuthorizationException;
        }
    }
}
