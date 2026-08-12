<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LiveSessionAttendanceSegment extends Model
{
    protected $fillable = ['attendance_id', 'joined_at', 'last_seen_at', 'left_at', 'duration_seconds', 'leave_reason'];

    protected $casts = ['joined_at' => 'datetime', 'last_seen_at' => 'datetime', 'left_at' => 'datetime'];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(LiveSessionAttendance::class);
    }
}
