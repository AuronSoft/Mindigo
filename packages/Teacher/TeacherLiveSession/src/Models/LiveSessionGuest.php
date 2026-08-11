<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;

final class LiveSessionGuest extends Model
{
    protected $fillable = ['live_session_id', 'guest_link_id', 'name', 'email', 'access_token_hash', 'admission_status', 'admitted_by', 'admitted_at', 'denied_at', 'removed_at', 'last_seen_at', 'microphone_enabled', 'camera_enabled', 'screen_sharing', 'connection_id'];

    protected $hidden = ['access_token_hash'];

    protected $casts = [
        'admission_status' => ParticipantAdmissionStatus::class,
        'admitted_at' => 'datetime', 'denied_at' => 'datetime', 'removed_at' => 'datetime', 'last_seen_at' => 'datetime',
        'microphone_enabled' => 'boolean', 'camera_enabled' => 'boolean', 'screen_sharing' => 'boolean',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function admittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admitted_by');
    }
}
