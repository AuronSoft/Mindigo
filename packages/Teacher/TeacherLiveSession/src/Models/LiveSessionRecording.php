<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mindigo\Auth\Models\User;

final class LiveSessionRecording extends Model
{
    protected $fillable = ['live_session_id', 'provider', 'provider_recording_id', 'provider_play_url', 'initiated_by', 'status', 'mime_type', 'storage_disk', 'storage_path', 'size_bytes', 'duration_seconds', 'started_at', 'ended_at', 'failure_reason'];

    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(LiveSessionRecordingChunk::class, 'recording_id');
    }
}
