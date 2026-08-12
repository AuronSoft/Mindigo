<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Str;
use Mindigo\TeacherLiveSession\Enums\ProviderSyncStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Throwable;

final class LiveProviderSyncService
{
    public function __construct(private readonly LiveMeetingProviderRegistry $providers) {}

    public function sync(LiveSession $session): bool
    {
        if (! $session->provider->isExternal() || blank($session->provider_meeting_id)) {
            return false;
        }

        try {
            $result = $this->providers->resolve($session->provider)->sync($session);
            $session->update([
                'provider_status' => $result->status,
                'provider_metadata' => $result->metadata,
                'sync_status' => ProviderSyncStatus::Synced,
                'last_synced_at' => now(),
                'sync_error' => null,
            ]);

            return true;
        } catch (Throwable $exception) {
            report($exception);
            $session->update([
                'sync_status' => ProviderSyncStatus::Failed,
                'last_synced_at' => now(),
                'sync_error' => Str::limit(class_basename($exception).': '.$exception->getMessage(), 1000),
            ]);

            return false;
        }
    }

    public function syncDue(int $limit = 100): array
    {
        $stats = ['synced' => 0, 'failed' => 0];
        LiveSession::query()->whereIn('provider', ['google_meet', 'zoom'])
            ->whereNotNull('provider_meeting_id')
            ->whereIn('status', ['scheduled', 'waiting', 'live'])
            ->where(fn ($query) => $query->whereNull('last_synced_at')->orWhere('last_synced_at', '<=', now()->subMinutes(5)))
            ->orderByRaw('last_synced_at IS NULL DESC')->orderBy('last_synced_at')->limit($limit)->get()
            ->each(function (LiveSession $session) use (&$stats): void {
                $this->sync($session) ? $stats['synced']++ : $stats['failed']++;
            });

        return $stats;
    }
}
