<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Collection;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionAttendance;

final class LiveSessionAttendanceReportService
{
    public function rows(LiveSession $session): Collection
    {
        $session->loadMissing('classroom');
        $attendances = $session->attendances()->with(['segments'])->get()->keyBy('user_id');

        return $session->classroom->students()->wherePivot('status', 'active')->get(['users.id', 'users.name', 'users.email'])
            ->map(function ($student) use ($attendances): array {
                /** @var LiveSessionAttendance|null $attendance */
                $attendance = $attendances->get($student->id);
                $activeSeconds = $attendance?->segments->whereNull('left_at')->sum(
                    fn ($segment) => $segment->joined_at->diffInSeconds($segment->last_seen_at),
                ) ?? 0;
                $totalSeconds = ($attendance?->total_seconds ?? 0) + $activeSeconds;
                $engagementPoints = ($attendance?->chat_messages_count ?? 0) * 2
                    + ($attendance?->reactions_count ?? 0)
                    + ($attendance?->hands_raised_count ?? 0) * 3
                    + ($attendance?->poll_votes_count ?? 0) * 5;

                return [
                    'user_id' => $student->id, 'name' => $student->name, 'email' => $student->email,
                    'status' => $attendance?->attendance_status ?? 'absent',
                    'joined_at' => $attendance?->joined_at, 'left_at' => $attendance?->left_at,
                    'total_seconds' => $totalSeconds, 'join_count' => $attendance?->join_count ?? 0,
                    'late_minutes' => $attendance?->late_minutes ?? 0,
                    'chat_messages_count' => $attendance?->chat_messages_count ?? 0,
                    'reactions_count' => $attendance?->reactions_count ?? 0,
                    'hands_raised_count' => $attendance?->hands_raised_count ?? 0,
                    'poll_votes_count' => $attendance?->poll_votes_count ?? 0,
                    'engagement_score' => min(100, $engagementPoints),
                ];
            });
    }

    public function summary(Collection $rows): array
    {
        return [
            'total' => $rows->count(),
            'present' => $rows->whereIn('status', ['present', 'late', 'partial'])->count(),
            'late' => $rows->where('status', 'late')->count(),
            'absent' => $rows->where('status', 'absent')->count(),
            'average_minutes' => $rows->isEmpty() ? 0 : (int) round($rows->avg('total_seconds') / 60),
        ];
    }
}
