<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mindigo\Auth\Models\User;

class LiveSessionAttendance extends Model
{
    protected $table = 'live_session_attendances';

    protected $fillable = [
        'live_session_id',
        'user_id',
        'joined_at',
        'left_at',
        'last_seen_at',
        'total_seconds',
        'join_count',
        'late_minutes',
        'attendance_status',
        'chat_messages_count',
        'reactions_count',
        'hands_raised_count',
        'poll_votes_count',
        'microphone_seconds',
        'camera_seconds',
        'media_last_counted_at',
        'finalized_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'finalized_at' => 'datetime',
        'media_last_counted_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function segments(): HasMany
    {
        return $this->hasMany(LiveSessionAttendanceSegment::class, 'attendance_id');
    }
}
