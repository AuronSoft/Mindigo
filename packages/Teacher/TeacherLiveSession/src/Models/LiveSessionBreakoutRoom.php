<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class LiveSessionBreakoutRoom extends Model
{
    protected $fillable = ['live_session_id', 'created_by', 'name', 'status', 'position', 'duration_minutes', 'opened_at', 'closes_at', 'closed_at'];

    protected $casts = ['opened_at' => 'datetime', 'closes_at' => 'datetime', 'closed_at' => 'datetime'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LiveSessionBreakoutAssignment::class, 'breakout_room_id');
    }
}
