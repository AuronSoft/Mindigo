<?php

namespace Mindigo\TeacherLiveSession\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionAttendance;
use Mindigo\TeacherLiveSession\Models\LiveSessionAttendanceSegment;

final class LiveSessionAttendanceService
{
    private const STALE_AFTER_SECONDS = 30;

    private const LATE_GRACE_MINUTES = 10;

    public function heartbeat(LiveSession $session, User $user): LiveSessionAttendance
    {
        return DB::transaction(function () use ($session, $user): LiveSessionAttendance {
            $attendance = LiveSessionAttendance::query()->firstOrCreate(
                ['live_session_id' => $session->id, 'user_id' => $user->id],
                ['joined_at' => now(), 'attendance_status' => 'present'],
            );
            $attendance = LiveSessionAttendance::query()->lockForUpdate()->findOrFail($attendance->id);
            $active = $attendance->segments()->whereNull('left_at')->latest('id')->first();

            if ($active && $active->last_seen_at->lt(now()->subSeconds(self::STALE_AFTER_SECONDS))) {
                $this->closeSegment($active, $active->last_seen_at, 'connection_lost');
                $active = null;
            }

            if (! $active) {
                $active = $attendance->segments()->create(['joined_at' => now(), 'last_seen_at' => now()]);
                $attendance->increment('join_count');
            } elseif ($active->last_seen_at->lte(now()->subSeconds(10))) {
                $active->update(['last_seen_at' => now(), 'duration_seconds' => $active->joined_at->diffInSeconds(now())]);
            }

            $firstJoin = $attendance->joined_at ?? now();
            $lateMinutes = max(0, (int) $session->scheduled_start->diffInMinutes($firstJoin, false));
            $attendance->update([
                'joined_at' => $firstJoin,
                'left_at' => null,
                'last_seen_at' => now(),
                'late_minutes' => $lateMinutes,
                'attendance_status' => $lateMinutes > self::LATE_GRACE_MINUTES ? 'late' : 'present',
                'finalized_at' => null,
            ]);

            return $attendance->fresh();
        });
    }

    public function leave(LiveSession $session, User $user, string $reason = 'left'): void
    {
        DB::transaction(function () use ($session, $user, $reason): void {
            $attendance = LiveSessionAttendance::query()->where('live_session_id', $session->id)
                ->where('user_id', $user->id)->lockForUpdate()->first();
            if (! $attendance) {
                return;
            }

            $active = $attendance->segments()->whereNull('left_at')->latest('id')->first();
            if ($active) {
                $this->closeSegment($active, now(), $reason);
            }
            $this->refreshAggregate($attendance, now(), false);
        });
    }

    public function incrementEngagement(LiveSession $session, User $user, string $metric): void
    {
        $allowed = ['chat_messages_count', 'reactions_count', 'hands_raised_count', 'poll_votes_count'];
        if (! in_array($metric, $allowed, true)) {
            return;
        }

        $attendance = $this->heartbeat($session, $user);
        LiveSessionAttendance::query()->whereKey($attendance->id)->increment($metric);
    }

    public function finalize(LiveSession $session): void
    {
        DB::transaction(function () use ($session): void {
            $session->loadMissing('classroom');
            $session->classroom->students()->wherePivot('status', 'active')->pluck('users.id')->each(
                fn (int $userId) => LiveSessionAttendance::query()->firstOrCreate(
                    ['live_session_id' => $session->id, 'user_id' => $userId],
                    ['attendance_status' => 'absent', 'join_count' => 0],
                ),
            );

            LiveSessionAttendance::query()->where('live_session_id', $session->id)->lockForUpdate()->get()
                ->each(function (LiveSessionAttendance $attendance) use ($session): void {
                    $attendance->segments()->whereNull('left_at')->get()->each(function (LiveSessionAttendanceSegment $segment) use ($session): void {
                        $end = $segment->last_seen_at->copy()->addSeconds(20);
                        if ($session->ended_at && $end->gt($session->ended_at)) {
                            $end = $session->ended_at;
                        }
                        $this->closeSegment($segment, $end, 'session_ended');
                    });
                    $this->refreshAggregate($attendance, $session->ended_at ?? now(), true);
                });
        });
    }

    private function closeSegment(LiveSessionAttendanceSegment $segment, CarbonInterface $leftAt, string $reason): void
    {
        $leftAt = $leftAt->lt($segment->joined_at) ? $segment->joined_at : $leftAt;
        $segment->update([
            'last_seen_at' => $leftAt,
            'left_at' => $leftAt,
            'duration_seconds' => $segment->joined_at->diffInSeconds($leftAt),
            'leave_reason' => $reason,
        ]);
    }

    private function refreshAggregate(LiveSessionAttendance $attendance, CarbonInterface $leftAt, bool $final): void
    {
        $total = (int) $attendance->segments()->sum('duration_seconds');
        $session = $attendance->session;
        $planned = max(1, $session->scheduled_start->diffInSeconds($session->scheduled_end ?? $leftAt));
        $status = match (true) {
            $attendance->join_count === 0 => 'absent',
            $attendance->late_minutes > self::LATE_GRACE_MINUTES => 'late',
            $total < $planned * 0.5 => 'partial',
            default => 'present',
        };
        $attendance->update([
            'left_at' => $leftAt,
            'total_seconds' => $total,
            'attendance_status' => $status,
            'finalized_at' => $final ? now() : null,
        ]);
    }
}
