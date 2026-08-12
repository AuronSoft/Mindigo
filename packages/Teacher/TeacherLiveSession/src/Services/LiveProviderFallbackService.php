<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Data\SessionContext;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Enums\ProviderSyncStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Notifications\LiveSessionProviderChanged;

final class LiveProviderFallbackService
{
    public function __construct(
        private readonly LiveMeetingProviderRegistry $providers,
        private readonly AuditLogService $audit,
    ) {}

    public function switchToNative(LiveSession $session, User $actor): LiveSession
    {
        abort_unless($session->provider->isExternal(), 422);
        abort_unless(in_array($session->status, ['draft', 'scheduled'], true) && $session->started_at === null, 422);

        $oldProvider = $session->provider;
        $meeting = $this->providers->resolve(LiveSessionProvider::Native)->create(new SessionContext(
            classroomId: (int) $session->classroom_id,
            teacherId: (int) $session->teacher_id,
            title: $session->title,
            description: $session->description,
            scheduledStart: $session->scheduled_start,
            scheduledEnd: $session->scheduled_end,
            idempotencyKey: $session->idempotency_key.'-native',
        ));

        $updated = DB::transaction(function () use ($session, $meeting, $oldProvider): LiveSession {
            $locked = LiveSession::query()->lockForUpdate()->findOrFail($session->getKey());
            abort_unless($locked->provider === $oldProvider && in_array($locked->status, ['draft', 'scheduled'], true) && $locked->started_at === null, 409);
            $locked->update([
                'provider' => LiveSessionProvider::Native,
                'provider_meeting_id' => $meeting->meetingId,
                'provider_join_url' => $meeting->joinUrl,
                'provider_host_url' => $meeting->hostUrl,
                'provider_status' => $meeting->status,
                'provider_metadata' => [...$meeting->metadata, 'fallback_from' => $oldProvider->value, 'fallback_at' => now()->toIso8601String()],
                'fallback_provider' => LiveSessionProvider::Native,
                'sync_status' => ProviderSyncStatus::NotRequired,
                'sync_error' => null,
                'last_synced_at' => now(),
                'room_name' => $meeting->roomName,
            ]);

            return $locked->fresh(['classroom.students']);
        });

        $this->audit->record('provider_fallback', 'teacher_live_session', ['provider' => $oldProvider->value], ['provider' => LiveSessionProvider::Native->value], auditable: $updated, user: $actor);
        $students = $updated->classroom?->students?->where('pivot.status', 'active') ?? collect();
        if ($students->isNotEmpty()) {
            Notification::send($students, new LiveSessionProviderChanged(
                (int) $updated->getKey(),
                $updated->title,
                $updated->classroom->name,
                route('student.live-sessions.index'),
            ));
        }

        return $updated;
    }
}
