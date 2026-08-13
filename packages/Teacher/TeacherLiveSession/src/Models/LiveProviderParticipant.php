<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LiveProviderParticipant extends Model
{
    protected $fillable = ['live_session_id', 'provider', 'provider_participant_id', 'provider_session_id', 'display_name', 'email', 'joined_at', 'left_at', 'duration_seconds', 'metadata'];

    protected $casts = ['joined_at' => 'datetime', 'left_at' => 'datetime', 'metadata' => 'array'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }
}
