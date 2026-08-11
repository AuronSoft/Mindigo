<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;

class LiveSessionParticipant extends Model
{
    protected $fillable = ['live_session_id', 'user_id', 'role', 'admission_status', 'admitted_by', 'admitted_at', 'denied_at', 'removed_at', 'last_seen_at', 'microphone_enabled', 'camera_enabled', 'screen_sharing', 'connection_id', 'hand_raised_at', 'force_muted_at', 'recording_consented_at'];

    protected $casts = [
        'role' => LiveParticipantRole::class,
        'admission_status' => ParticipantAdmissionStatus::class,
        'admitted_at' => 'datetime',
        'denied_at' => 'datetime',
        'removed_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'microphone_enabled' => 'boolean',
        'camera_enabled' => 'boolean',
        'screen_sharing' => 'boolean',
        'hand_raised_at' => 'datetime',
        'force_muted_at' => 'datetime',
        'recording_consented_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admitted_by');
    }
}
