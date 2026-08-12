<?php

namespace Mindigo\TeacherLiveSession\Services;

use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;

final class LiveSessionOperationalMonitor
{
    public function alerts(): array
    {
        $alerts = [];
        $failedSyncs = LiveSession::query()->where('sync_status', 'failed')->where('updated_at', '>=', now()->subDay())->count();
        $staleRooms = LiveSession::query()->where('status', 'live')->where('scheduled_end', '<', now()->subHour())->count();
        $failedRecordings = LiveSessionRecording::query()->where('status', 'failed')->where('updated_at', '>=', now()->subDay())->count();

        if ($failedSyncs > 0) {
            $alerts[] = ['severity' => $failedSyncs >= 5 ? 'critical' : 'warning', 'code' => 'provider_sync_failures', 'count' => $failedSyncs];
        }
        if ($staleRooms > 0) {
            $alerts[] = ['severity' => 'critical', 'code' => 'stale_live_rooms', 'count' => $staleRooms];
        }
        if ($failedRecordings > 0) {
            $alerts[] = ['severity' => 'warning', 'code' => 'recording_failures', 'count' => $failedRecordings];
        }

        return $alerts;
    }
}
