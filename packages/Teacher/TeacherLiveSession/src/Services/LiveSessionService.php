<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Data\SessionContext;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Enums\ProviderSyncStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;

final class LiveSessionService
{
    public function __construct(private readonly LiveMeetingProviderRegistry $providers) {}

    public function getSessionsByTeacher(int|string $teacherId, ?int $classroomId = null, int $perPage = 10): LengthAwarePaginator
    {
        return LiveSession::query()
            ->byTeacher($teacherId)
            ->when($classroomId, fn ($query) => $query->where('classroom_id', $classroomId))
            ->with(['classroom.course', 'schedule.lesson'])
            ->latest('scheduled_start')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, User $actor): LiveSession
    {
        return DB::transaction(function () use ($data, $actor): LiveSession {
            $providerKey = LiveSessionProvider::from($data['provider'] ?? LiveSessionProvider::Native->value);
            $idempotencyKey = $data['idempotency_key'] ?? (string) Str::uuid();
            $context = new SessionContext(
                classroomId: (int) $data['classroom_id'],
                teacherId: (int) $actor->getKey(),
                title: $data['title'],
                description: $data['description'] ?? null,
                scheduledStart: Carbon::parse($data['scheduled_start']),
                scheduledEnd: filled($data['scheduled_end'] ?? null) ? Carbon::parse($data['scheduled_end']) : null,
                idempotencyKey: $idempotencyKey,
            );
            $meeting = $this->providers->resolve($providerKey)->create($context);

            return LiveSession::query()->create([
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
        });
    }

    public function update(LiveSession $session, array $data): LiveSession
    {
        return DB::transaction(function () use ($session, $data): LiveSession {
            unset($data['provider']);
            $session->update($data);
            $meeting = $this->providers->resolve($session->provider)->update($session->refresh());
            $session->update([
                'provider_meeting_id' => $meeting->meetingId,
                'provider_join_url' => $meeting->joinUrl,
                'provider_host_url' => $meeting->hostUrl,
                'provider_metadata' => $meeting->metadata,
                'provider_status' => $meeting->status,
            ]);

            return $session->fresh(['classroom.course', 'schedule.lesson']);
        });
    }

    public function delete(LiveSession $session): void
    {
        $session->delete();
    }

    public function start(LiveSession $session, User $actor): LiveSession
    {
        return DB::transaction(function () use ($session, $actor): LiveSession {
            if ($session->status === 'scheduled') {
                $this->providers->resolve($session->provider)->start($session, $actor);
                $session->update([
                    'status' => 'live',
                    'started_at' => now(),
                    'provider_status' => 'live',
                ]);
            }

            return $session->fresh();
        });
    }

    public function end(LiveSession $session, User $actor): LiveSession
    {
        return DB::transaction(function () use ($session, $actor): LiveSession {
            $this->providers->resolve($session->provider)->end($session, $actor);
            $session->update([
                'status' => 'ended',
                'ended_at' => now(),
                'ended_by' => $actor->getKey(),
                'provider_status' => 'ended',
            ]);

            return $session->fresh();
        });
    }

    public function join(LiveSession $session, User $actor): array
    {
        $result = $this->providers->resolve($session->provider)->join($session, $actor);
        $this->recordJoin($session, $actor->getKey());

        return [
            'mode' => $result->mode,
            'url' => $result->url,
            'token' => $result->token,
            'metadata' => $result->metadata,
        ];
    }

    public function recordJoin(LiveSession $session, int|string $userId): void
    {
        $attendance = $session->attendances()->firstOrCreate(
            ['user_id' => $userId],
            ['joined_at' => now()],
        );

        if (! $attendance->joined_at) {
            $attendance->update(['joined_at' => now()]);
        }
    }
}
