<?php

namespace Mindigo\TeacherOnboarding\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\Notification\Notifications\TeacherApplicationProvisioningNotification;
use Mindigo\TeacherCourse\Models\TeacherProfile;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Mindigo\TeacherOnboarding\Models\TeacherApplicationInterview;

class TeacherApplicationProvisioningService
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function approve(TeacherApplication $application, User $admin, ?string $note = null): TeacherApplication
    {
        return DB::transaction(function () use ($application, $admin, $note): TeacherApplication {
            $application = $this->lockedApplication($application);
            $this->ensureCanApprove($application);

            $teacher = $application->user;
            $previousRole = $application->provisioned_user_role ?: $teacher->role;

            $oldValues = [
                'application_status' => $application->status,
                'teacher_provision_status' => $application->teacher_provision_status,
                'user_role' => $teacher->role,
            ];

            $teacher->forceFill(['role' => 'teacher'])->save();

            $this->profileFor($application, $teacher);

            $application->forceFill([
                'status' => TeacherApplication::STATUS_APPROVED,
                'teacher_provision_status' => TeacherApplication::PROVISION_ACTIVE,
                'provisioned_user_role' => $previousRole,
                'provisioned_by' => $admin->getAuthIdentifier(),
                'provisioned_at' => now(),
                'teacher_suspended_at' => null,
                'teacher_revoked_at' => null,
                'provisioning_note' => $note,
                'reviewed_by' => $admin->getAuthIdentifier(),
                'reviewed_at' => now(),
            ])->save();

            $this->auditProvisioning('approve', $application, $admin, $oldValues, [
                'application_status' => $application->status,
                'teacher_provision_status' => $application->teacher_provision_status,
                'user_role' => 'teacher',
                'note' => $note,
            ]);

            DB::afterCommit(fn () => $this->notifyApplicant($application, 'approved', $note));

            return $application->refresh();
        });
    }

    public function suspend(TeacherApplication $application, User $admin, string $note): TeacherApplication
    {
        return DB::transaction(function () use ($application, $admin, $note): TeacherApplication {
            $application = $this->lockedApplication($application);
            $this->ensureCanSuspend($application);

            $teacher = $application->user;
            $oldValues = [
                'application_status' => $application->status,
                'teacher_provision_status' => $application->teacher_provision_status,
                'user_role' => $teacher?->role,
            ];

            if ($teacher?->isTeacher()) {
                $teacher->forceFill(['role' => $application->provisioned_user_role ?: 'student'])->save();
            }

            $application->forceFill([
                'status' => TeacherApplication::STATUS_SUSPENDED,
                'teacher_provision_status' => TeacherApplication::PROVISION_SUSPENDED,
                'provisioned_by' => $admin->getAuthIdentifier(),
                'teacher_suspended_at' => now(),
                'provisioning_note' => $note,
                'reviewed_by' => $admin->getAuthIdentifier(),
                'reviewed_at' => now(),
            ])->save();

            $this->auditProvisioning('suspend', $application, $admin, $oldValues, [
                'application_status' => $application->status,
                'teacher_provision_status' => $application->teacher_provision_status,
                'user_role' => $teacher?->role,
                'note' => $note,
            ]);

            DB::afterCommit(fn () => $this->notifyApplicant($application, 'suspended', $note));

            return $application->refresh();
        });
    }

    public function revoke(TeacherApplication $application, User $admin, string $note): TeacherApplication
    {
        return DB::transaction(function () use ($application, $admin, $note): TeacherApplication {
            $application = $this->lockedApplication($application);
            $this->ensureCanRevoke($application);

            $teacher = $application->user;
            $oldValues = [
                'application_status' => $application->status,
                'teacher_provision_status' => $application->teacher_provision_status,
                'user_role' => $teacher?->role,
            ];

            if ($teacher?->isTeacher()) {
                $teacher->forceFill(['role' => $application->provisioned_user_role ?: 'student'])->save();
            }

            $application->forceFill([
                'status' => TeacherApplication::STATUS_REVOKED,
                'teacher_provision_status' => TeacherApplication::PROVISION_REVOKED,
                'provisioned_by' => $admin->getAuthIdentifier(),
                'teacher_revoked_at' => now(),
                'provisioning_note' => $note,
                'reviewed_by' => $admin->getAuthIdentifier(),
                'reviewed_at' => now(),
            ])->save();

            $this->auditProvisioning('revoke', $application, $admin, $oldValues, [
                'application_status' => $application->status,
                'teacher_provision_status' => $application->teacher_provision_status,
                'user_role' => $teacher?->role,
                'note' => $note,
            ]);

            DB::afterCommit(fn () => $this->notifyApplicant($application, 'revoked', $note));

            return $application->refresh();
        });
    }

    private function lockedApplication(TeacherApplication $application): TeacherApplication
    {
        return TeacherApplication::query()
            ->with(['user', 'interviews'])
            ->whereKey($application->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function ensureCanApprove(TeacherApplication $application): void
    {
        if (! $application->user) {
            throw new AuthorizationException(__('teacher-onboarding::provisioning.missing_account'));
        }

        $hasPassedInterview = $application->interviews
            ->contains('result', TeacherApplicationInterview::RESULT_PASSED);

        if (! $hasPassedInterview) {
            throw new AuthorizationException(__('teacher-onboarding::provisioning.must_pass_interview'));
        }
    }

    private function ensureCanSuspend(TeacherApplication $application): void
    {
        if ($application->teacher_provision_status !== TeacherApplication::PROVISION_ACTIVE) {
            throw new AuthorizationException(__('teacher-onboarding::provisioning.invalid_suspend_state'));
        }
    }

    private function ensureCanRevoke(TeacherApplication $application): void
    {
        if (! in_array($application->teacher_provision_status, [
            TeacherApplication::PROVISION_ACTIVE,
            TeacherApplication::PROVISION_SUSPENDED,
        ], true)) {
            throw new AuthorizationException(__('teacher-onboarding::provisioning.invalid_revoke_state'));
        }
    }

    private function profileFor(TeacherApplication $application, User $teacher): TeacherProfile
    {
        return TeacherProfile::query()->firstOrCreate(
            ['user_id' => $teacher->id],
            [
                'headline' => $application->specialization,
                'biography' => $application->teaching_method ?: $application->experience_description,
                'specialization' => $application->specialization,
                'experience_years' => $application->experience_years ?? 0,
                'qualifications' => collect([$application->achievements, $application->current_organization])
                    ->filter()
                    ->values()
                    ->all(),
                'is_public' => false,
            ]
        );
    }

    private function auditProvisioning(string $action, TeacherApplication $application, User $admin, array $oldValues, array $newValues): void
    {
        $this->audit->record(
            action: 'teacher_application_'.$action,
            module: 'teacher-onboarding',
            oldValues: $oldValues,
            newValues: $newValues,
            metadata: [
                'performed_by' => $admin->id,
                'performed_at' => now()->toDateTimeString(),
                'application_code' => $application->application_code,
            ],
            auditable: $application,
            user: $admin
        );
    }

    private function notifyApplicant(TeacherApplication $application, string $action, ?string $note): void
    {
        $application->user?->notify(new TeacherApplicationProvisioningNotification(
            $application->application_code,
            $action,
            $note
        ));
    }
}
