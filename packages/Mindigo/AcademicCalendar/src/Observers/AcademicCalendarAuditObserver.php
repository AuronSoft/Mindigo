<?php

namespace Mindigo\AcademicCalendar\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mindigo\AcademicCalendar\Models\AcademicCalendarException;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

class AcademicCalendarAuditObserver
{
    private const MODULE = 'academic-calendar';

    public function __construct(private readonly AuditLogService $audit) {}

    public function created(Model $model): void
    {
        if (! $model instanceof ClassroomSchedule && ! $model instanceof AcademicCalendarException) {
            return;
        }

        $this->audit->record(
            action: $model instanceof AcademicCalendarException ? 'calendar_exception_created' : 'session_created',
            module: self::MODULE,
            newValues: $this->values($model, $model->getAttributes()),
            metadata: $this->metadata($model),
            auditable: $model,
            user: $this->actor($model),
        );
    }

    public function updated(Model $model): void
    {
        if ($model instanceof Classroom && ! $model->wasChanged('assistant_id')) {
            return;
        }

        if (! $this->supported($model) || $model->getChanges() === []) {
            return;
        }

        $changes = $model->getChanges();
        $previous = array_intersect_key($model->getPrevious(), $changes);
        $this->audit->record(
            action: $this->updatedAction($model, $previous, $changes),
            module: self::MODULE,
            oldValues: $this->values($model, $previous),
            newValues: $this->values($model, $changes),
            metadata: $this->metadata($model),
            auditable: $model,
            user: $this->actor($model),
        );
    }

    public function deleted(Model $model): void
    {
        if (! $model instanceof AcademicCalendarException) {
            return;
        }

        $this->audit->record(
            action: 'calendar_exception_deleted',
            module: self::MODULE,
            oldValues: $this->values($model, $model->getOriginal()),
            metadata: $this->metadata($model),
            auditable: $model,
            user: $this->actor($model),
        );
    }

    private function supported(Model $model): bool
    {
        return $model instanceof Classroom || $model instanceof ClassroomSchedule || $model instanceof AcademicCalendarException;
    }

    private function updatedAction(Model $model, array $previous, array $changes): string
    {
        if ($model instanceof Classroom) {
            return match (true) {
                empty($changes['assistant_id']) => 'assistant_removed',
                empty($previous['assistant_id']) => 'assistant_assigned',
                default => 'assistant_replaced',
            };
        }

        if ($model instanceof AcademicCalendarException) {
            return 'calendar_exception_updated';
        }

        return match (true) {
            ($changes['status'] ?? null) === ClassroomSchedule::STATUS_CANCELLED => 'session_cancelled',
            ($changes['status'] ?? null) === ClassroomSchedule::STATUS_COMPLETED => 'session_completed',
            ($changes['status'] ?? null) === ClassroomSchedule::STATUS_RESCHEDULED => 'session_rescheduled',
            ($changes['substitute_status'] ?? null) === ClassroomSchedule::SUBSTITUTE_ACCEPTED => 'substitute_accepted',
            ($changes['substitute_status'] ?? null) === ClassroomSchedule::SUBSTITUTE_DECLINED => 'substitute_declined',
            array_key_exists('substitute_teacher_id', $changes) && empty($changes['substitute_teacher_id']) => 'substitute_removed',
            array_key_exists('substitute_teacher_id', $changes) => 'substitute_assigned',
            array_intersect_key($changes, array_flip(['session_date', 'start_time', 'end_time'])) !== [] => 'session_rescheduled',
            default => 'session_updated',
        };
    }

    private function metadata(Model $model): array
    {
        return array_filter(match (true) {
            $model instanceof Classroom => ['classroom_id' => $model->id, 'course_id' => $model->course_id],
            $model instanceof ClassroomSchedule => [
                'classroom_id' => $model->classroom_id,
                'course_id' => $model->classroom?->course_id,
                'lesson_id' => $model->lesson_id,
            ],
            $model instanceof AcademicCalendarException => [
                'exception_scope' => $model->classroom_id ? 'classroom' : ($model->course_id ? 'course' : 'global'),
                'classroom_id' => $model->classroom_id,
                'course_id' => $model->course_id,
            ],
            default => [],
        }, fn ($value) => $value !== null);
    }

    private function values(Model $model, array $values): array
    {
        $allowed = match (true) {
            $model instanceof Classroom => ['assistant_id'],
            $model instanceof ClassroomSchedule => [
                'classroom_id', 'lesson_id', 'type', 'delivery_mode', 'status', 'title', 'session_date',
                'start_time', 'end_time', 'location', 'meeting_url', 'makeup_reason', 'cancel_reason',
                'reschedule_reason', 'substitute_teacher_id', 'substitute_status', 'makeup_for_schedule_id',
                'rescheduled_from_id', 'published_at', 'created_by', 'updated_by',
            ],
            $model instanceof AcademicCalendarException => [
                'course_id', 'classroom_id', 'exception_date', 'kind', 'title', 'reason', 'created_by',
            ],
            default => [],
        };

        return collect($values)->only($allowed)->all();
    }

    private function actor(Model $model): ?User
    {
        $authenticatedUser = Auth::user();

        if ($authenticatedUser instanceof User) {
            return $authenticatedUser;
        }

        $actorId = $model instanceof ClassroomSchedule
            ? ($model->updated_by ?: $model->created_by)
            : ($model->created_by ?? null);

        return $actorId ? User::query()->find($actorId) : null;
    }
}
