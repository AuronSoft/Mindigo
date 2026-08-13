<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LiveProviderWebhookEvent extends Model
{
    protected $fillable = ['provider', 'event_id', 'event_type', 'live_session_id', 'payload', 'status', 'failure_reason', 'received_at', 'processed_at'];

    protected $casts = ['payload' => 'encrypted:array', 'received_at' => 'datetime', 'processed_at' => 'datetime'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }
}
