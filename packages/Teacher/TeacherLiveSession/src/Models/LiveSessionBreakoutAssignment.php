<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LiveSessionBreakoutAssignment extends Model
{
    protected $fillable = ['breakout_room_id', 'participant_id', 'assigned_by', 'joined_at', 'left_at'];

    protected $casts = ['joined_at' => 'datetime', 'left_at' => 'datetime'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(LiveSessionBreakoutRoom::class, 'breakout_room_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(LiveSessionParticipant::class);
    }
}
