<?php

namespace Mindigo\TeacherOnboarding\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\Notification\Notifications\TeacherApplicationInterviewNotification;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Mindigo\TeacherOnboarding\Models\TeacherApplicationInterview;

class TeacherApplicationInterviewService
{
    public function create(TeacherApplication $application, User $admin, array $data): TeacherApplicationInterview
    {
        $this->ensureCanSchedule($application);

        return DB::transaction(function () use ($application, $admin, $data): TeacherApplicationInterview {
            $application = TeacherApplication::query()->whereKey($application->getKey())->lockForUpdate()->firstOrFail();
            $this->ensureCanSchedule($application);

            $interview = TeacherApplicationInterview::query()->create([
                ...$data,
                'teacher_application_id' => $application->id,
                'interviewer_id' => $admin->id,
            ]);

            $application->forceFill([
                'status' => TeacherApplication::STATUS_INTERVIEW_SCHEDULED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ])->save();

            DB::afterCommit(fn () => $this->notifyApplicant($application, 'scheduled', $interview));

            return $interview->refresh();
        });
    }

    public function updateSchedule(TeacherApplicationInterview $interview, User $admin, array $data): TeacherApplicationInterview
    {
        return DB::transaction(function () use ($interview, $admin, $data): TeacherApplicationInterview {
            $interview = TeacherApplicationInterview::query()
                ->whereKey($interview->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $interview->forceFill([
                ...$data,
                'interviewer_id' => $admin->id,
            ])->save();

            DB::afterCommit(fn () => $this->notifyApplicant($interview->application, 'rescheduled', $interview));

            return $interview->refresh();
        });
    }

    public function evaluate(TeacherApplicationInterview $interview, User $admin, array $data): TeacherApplicationInterview
    {
        return DB::transaction(function () use ($interview, $admin, $data): TeacherApplicationInterview {
            $interview = TeacherApplicationInterview::query()
                ->whereKey($interview->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $interview->forceFill([
                ...$data,
                'evaluated_at' => now(),
                'evaluated_by' => $admin->id,
            ])->save();

            $applicationStatus = $data['result'] === TeacherApplicationInterview::RESULT_NEED_SECOND_INTERVIEW
                ? TeacherApplication::STATUS_SCREENING
                : TeacherApplication::STATUS_INTERVIEWED;

            $interview->application->forceFill([
                'status' => $applicationStatus,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'status_note' => $data['overall_comment'] ?? null,
            ])->save();

            DB::afterCommit(fn () => $this->notifyApplicant($interview->application, 'result', $interview));

            return $interview->refresh();
        });
    }

    public function detail(TeacherApplicationInterview $interview): TeacherApplicationInterview
    {
        return $interview->load([
            'application.user:id,name,email',
            'application.subject:id,name',
            'application.category:id,name',
            'interviewer:id,name,email',
            'evaluator:id,name,email',
        ]);
    }

    private function ensureCanSchedule(TeacherApplication $application): void
    {
        if ($application->status !== TeacherApplication::STATUS_SCREENING) {
            throw new AuthorizationException(__('teacher-onboarding::interview.invalid_schedule_state'));
        }
    }

    private function notifyApplicant(TeacherApplication $application, string $event, TeacherApplicationInterview $interview): void
    {
        $applicant = $application->user;

        if (! $applicant) {
            return;
        }

        $applicant->notify(new TeacherApplicationInterviewNotification(
            $application->application_code,
            $event,
            $interview->scheduled_at->format('d/m/Y H:i'),
            $interview->result,
        ));
    }
}
