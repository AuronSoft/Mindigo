<?php

namespace Mindigo\TeacherLiveSession\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Mindigo\TeacherLiveSession\Enums\ProviderSyncStatus;
use Mindigo\TeacherLiveSession\Models\LiveProviderParticipant;
use Mindigo\TeacherLiveSession\Models\LiveProviderWebhookEvent;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;
use Throwable;

final class LiveProviderWebhookProcessor
{
    public function __construct(private readonly LiveProviderSyncService $sync) {}

    public function process(LiveProviderWebhookEvent $event): void
    {
        try {
            DB::transaction(function () use ($event): void {
                $event = LiveProviderWebhookEvent::query()->lockForUpdate()->findOrFail($event->id);
                if ($event->processed_at !== null) {
                    return;
                }

                if ($event->event_type === 'calendar.changed') {
                    $this->sync->syncDue();
                    $event->update(['status' => 'processed', 'processed_at' => now(), 'failure_reason' => null]);

                    return;
                }

                $session = $this->resolveSession($event);
                if ($session) {
                    $event->live_session_id = $session->id;
                    $this->apply($event, $session);
                }

                $event->update(['status' => $session ? 'processed' : 'ignored', 'processed_at' => now(), 'failure_reason' => null]);
            });
        } catch (Throwable $exception) {
            $event->update(['status' => 'failed', 'failure_reason' => mb_substr($exception->getMessage(), 0, 1000)]);
            throw $exception;
        }
    }

    private function resolveSession(LiveProviderWebhookEvent $event): ?LiveSession
    {
        $payload = $event->payload;
        $meetingId = $event->provider === 'zoom'
            ? data_get($payload, 'payload.object.id')
            : data_get($payload, 'meeting_id', data_get($payload, 'resource.space.name', data_get($payload, 'data.resource.space.name', data_get($payload, 'data.resource.space'))));

        $query = LiveSession::query()->where('provider', $event->provider);
        if ($meetingId) {
            $query->where(function ($query) use ($meetingId): void {
                $query->where('provider_meeting_id', (string) $meetingId)
                    ->orWhere('provider_metadata->space_name', (string) $meetingId)
                    ->orWhere('provider_metadata->conference_id', basename((string) $meetingId))
                    ->orWhere('provider_metadata->uuid', (string) $meetingId);
            });

            return $query->first();
        }

        return null;
    }

    private function apply(LiveProviderWebhookEvent $event, LiveSession $session): void
    {
        $type = strtolower($event->event_type);
        $payload = $event->payload;
        $metadata = [...($session->provider_metadata ?? []), 'last_webhook_event' => $type, 'last_webhook_at' => now()->toIso8601String()];
        $updates = ['provider_metadata' => $metadata, 'last_synced_at' => now(), 'sync_status' => ProviderSyncStatus::Synced, 'sync_error' => null];

        if (str_contains($type, 'started')) {
            $updates += ['status' => 'live', 'provider_status' => 'started', 'started_at' => $session->started_at ?? now()];
        } elseif (str_contains($type, 'ended')) {
            $updates += ['status' => 'ended', 'provider_status' => 'ended', 'ended_at' => $session->ended_at ?? now()];
        } elseif (str_contains($type, 'deleted') || str_contains($type, 'cancelled')) {
            $updates += ['status' => 'cancelled', 'provider_status' => 'cancelled', 'cancelled_at' => $session->cancelled_at ?? now(), 'cancel_reason' => 'Provider cancelled the meeting.'];
        }

        $session->update($updates);

        if (str_contains($type, 'participant') || str_contains($type, 'attendee')) {
            $this->participant($event, $session);
        }
        if (str_contains($type, 'recording')) {
            $this->recording($event, $session);
        }
    }

    private function participant(LiveProviderWebhookEvent $event, LiveSession $session): void
    {
        $object = data_get($event->payload, 'payload.object.participant', data_get($event->payload, 'resource', data_get($event->payload, 'data.resource', [])));
        $participantId = (string) data_get($object, 'id', data_get($object, 'participant'));
        $providerSessionId = (string) data_get($object, 'participant_user_id', data_get($object, 'name', $event->event_id));
        if ($participantId === '') {
            return;
        }

        $joinedAt = $this->date(data_get($object, 'join_time', data_get($object, 'startTime')));
        $leftAt = $this->date(data_get($object, 'leave_time', data_get($object, 'endTime')));
        $participant = LiveProviderParticipant::query()->updateOrCreate(
            ['provider' => $event->provider, 'provider_session_id' => $providerSessionId],
            ['live_session_id' => $session->id, 'provider_participant_id' => $participantId, 'display_name' => data_get($object, 'user_name', data_get($object, 'displayName')), 'email' => data_get($object, 'email'), 'joined_at' => $joinedAt, 'left_at' => $leftAt, 'metadata' => Arr::except($object, ['email'])],
        );
        if ($participant->joined_at && $participant->left_at) {
            $participant->update(['duration_seconds' => max(0, $participant->joined_at->diffInSeconds($participant->left_at))]);
        }
    }

    private function recording(LiveProviderWebhookEvent $event, LiveSession $session): void
    {
        $object = data_get($event->payload, 'payload.object', data_get($event->payload, 'resource', data_get($event->payload, 'data.resource', [])));
        $files = data_get($object, 'recording_files', [$object]);
        foreach ($files as $file) {
            $id = (string) data_get($file, 'id', data_get($file, 'name'));
            if ($id === '') {
                continue;
            }
            LiveSessionRecording::query()->updateOrCreate(
                ['provider' => $event->provider, 'provider_recording_id' => $id],
                ['live_session_id' => $session->id, 'initiated_by' => $session->teacher_id, 'status' => str_contains(strtolower($event->event_type), 'completed') || str_contains(strtolower($event->event_type), 'generated') ? 'ready' : 'processing', 'mime_type' => data_get($file, 'file_type'), 'provider_play_url' => data_get($file, 'play_url', data_get($file, 'exportUri')), 'started_at' => $this->date(data_get($file, 'recording_start', data_get($object, 'start_time'))) ?? now(), 'ended_at' => $this->date(data_get($file, 'recording_end', data_get($object, 'end_time')))],
            );
        }
    }

    private function date(mixed $value): ?CarbonImmutable
    {
        return filled($value) ? CarbonImmutable::parse($value) : null;
    }
}
