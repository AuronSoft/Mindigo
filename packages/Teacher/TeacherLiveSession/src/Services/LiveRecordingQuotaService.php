<?php

namespace Mindigo\TeacherLiveSession\Services;

use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;

final class LiveRecordingQuotaService
{
    public function assertAvailable(int $teacherId): void
    {
        $used = (int) LiveSessionRecording::query()->whereHas('session', fn ($query) => $query->where('teacher_id', $teacherId))
            ->where('created_at', '>=', now()->startOfMonth())->sum('size_bytes');
        $quota = (int) config('live-media.recording.monthly_quota_gb', 20) * 1024 * 1024 * 1024;
        abort_if($quota > 0 && $used >= $quota, 422, __('teacher-live-session::app.validation.recording_quota_exceeded'));
    }
}
