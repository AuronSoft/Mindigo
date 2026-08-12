<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherLiveSession\Data\ProviderMeeting;
use Mindigo\TeacherLiveSession\Data\SessionContext;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Enums\ProviderSyncStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Throwable;

final class LiveSessionService
{
    public function __construct(
        private readonly LiveMeetingProviderRegistry $providers,
        private readonly AuditLogService $audit,
        private readonly LiveSessionAttendanceService $attendance,
        private readonly LiveProviderCircuitBreaker $circuit,
    ) {}

    public function getSessionsByTeacher(int|string $teacherId, ?int $classroomId = null, int $perPage = 10): LengthAwarePaginator
    {
        return LiveSession::query()
            ->where(function ($query) use ($teacherId): void {
                $query->where('teacher_id', $teacherId)
                    ->orWhereHas('classroom', fn ($classrooms) => $classrooms->where('assistant_id', $teacherId));
            })
            ->when($classroomId, fn ($query) => $query->where('classroom_id', $classroomId))
            ->with(['classroom.course', 'schedule.lesson', 'recordings' => fn ($query) => $query->where('status', 'ready')->latest()])
            ->latest('scheduled_start')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, User $actor): LiveSession
    {
        $idempotencyKey = $data['idempotency_key'] ?? (string) Str::uuid();

        return Cache::lock('live-session:create:'.$idempotencyKey, 30)->block(10, function () use ($data, $actor, $idempotencyKey): LiveSession {
            $existing = LiveSession::query()->where('idempotency_key', $idempotencyKey)->first();

            return $existing ?? $this->createOnce($data, $actor, $idempotencyKey);
        });
    }

    private function createOnce(array $data, User $actor, string $idempotencyKey): LiveSession
    {
        $providerKey = LiveSessionProvider::from($data['provider'] ?? LiveSessionProvider::Native->value);
        $context = new SessionContext(
            classroomId: (int) $data['classroom_id'],
            teacherId: (int) $actor->getKey(),
            title: $data['title'],
            description: $data['description'] ?? null,
            scheduledStart: Carbon::parse($data['scheduled_start']),
            scheduledEnd: filled($data['scheduled_end'] ?? null) ? Carbon::parse($data['scheduled_end']) : null,
            idempotencyKey: $idempotencyKey,
        );
        $requestedProvider = $providerKey;
        try {
            $this->circuit->assertAvailable($providerKey);
            $meeting = $this->providers->resolve($providerKey)->create($context);
            $this->circuit->recordSuccess($providerKey);
        } catch (Throwable $exception) {
            $this->circuit->recordFailure($providerKey);
            if (! $providerKey->isExternal()) {
                throw $exception;
            }
            report($exception);
            $providerKey = LiveSessionProvider::Native;
            $nativeMeeting = $this->providers->resolve($providerKey)->create($context);
            $meeting = new ProviderMeeting(
                roomName: $nativeMeeting->roomName, meetingId: $nativeMeeting->meetingId,
                joinUrl: $nativeMeeting->joinUrl, hostUrl: $nativeMeeting->hostUrl,
                status: $nativeMeeting->status,
                metadata: [...$nativeMeeting->metadata, 'fallback_from' => $requestedProvider->value, 'fallback_reason' => class_basename($exception)],
            );
        }

        $session = DB::transaction(function () use ($data, $actor, $providerKey, $idempotencyKey, $meeting): LiveSession {
            $session = LiveSession::query()->create([
                ...$data,
                'teacher_id' => $actor->getKey(),
                'created_by' => $actor->getKey(),
                'room_name' => $meeting->roomName,
                'idempotency_key' => $idempotencyKey,
                'provider' => $providerKey,
                'provider_meeting_id' => $meeting->meetingId,
                'provider_join_url' => $meeting->joinUrl,
                'provider_host_url' => $meeting->hostUrl,
                'provider_metadata' => $meeting->metadata,
                'provider_status' => $meeting->status,
                'fallback_provider' => LiveSessionProvider::Native,
                'sync_status' => $providerKey === LiveSessionProvider::Native
                    ? ProviderSyncStatus::NotRequired
                    : ProviderSyncStatus::Pending,
                'status' => 'scheduled',
            ]);

            if ($session->classroom_schedule_id) {
                ClassroomSchedule::query()
                    ->whereKey($session->classroom_schedule_id)
                    ->where('delivery_mode', ClassroomSchedule::DELIVERY_OFFLINE)
                    ->update(['delivery_mode' => ClassroomSchedule::DELIVERY_ONLINE]);
            }

            return $session;
        });

        $this->audit->record('created', 'teacher_live_session', newValues: $this->safeAuditValues($session), auditable: $session, user: $actor);

        return $session;
    }

    public function update(LiveSession $session, array $data): LiveSession
    {
        unset($data['provider']);
        $providerSnapshot = clone $session;
        $providerSnapshot->fill($data);
        $this->circuit->assertAvailable($session->provider);
        try {
            $meeting = $this->providers->resolve($session->provider)->update($providerSnapshot);
            $this->circuit->recordSuccess($session->provider);
        } catch (Throwable $exception) {
            $this->circuit->recordFailure($session->provider);
            throw $exception;
        }

        $oldValues = $this->safeAuditValues($session);
        $updated = DB::transaction(function () use ($session, $data, $meeting): LiveSession {
            $session->update([
                ...$data,
                'provider_meeting_id' => $meeting->meetingId,
                'provider_join_url' => $meeting->joinUrl,
                'provider_host_url' => $meeting->hostUrl,
                'provider_metadata' => $meeting->metadata,
                'provider_status' => $meeting->status,
            ]);

            return $session->fresh(['classroom.course', 'schedule.lesson']);
        });

        $this->audit->record('updated', 'teacher_live_session', $oldValues, $this->safeAuditValues($updated), auditable: $updated);

        return $updated;
    }

    public function delete(LiveSession $session): void
    {
        abort_if(in_array($session->status, ['waiting', 'live'], true), 422);
        $safeValues = $this->safeAuditValues($session);
        $session->delete();
        $this->audit->record('deleted', 'teacher_live_session', oldValues: $safeValues, auditable: $session);
    }

    public function join(LiveSession $session, User $actor): array
    {
        $result = $this->providers->resolve($session->provider)->join($session, $actor);
        $this->attendance->heartbeat($session, $actor);

        return [
            'mode' => $result->mode,
            'url' => $result->url,
            'token' => $result->token,
            'metadata' => $result->metadata,
        ];
    }

    public function recordJoin(LiveSession $session, int|string $userId): void
    {
        $user = User::query()->findOrFail($userId);
        $this->attendance->heartbeat($session, $user);
    }

    private function safeAuditValues(LiveSession $session): array
    {
        return $session->only([
            'classroom_id', 'classroom_schedule_id', 'teacher_id', 'title', 'provider',
            'scheduled_start', 'scheduled_end', 'status', 'session_type', 'room_settings',
        ]);
    }
}
