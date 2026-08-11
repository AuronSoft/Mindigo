<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class LiveSessionGuestLink extends Model
{
    protected $fillable = ['live_session_id', 'created_by', 'token_hash', 'max_uses', 'uses_count', 'expires_at', 'revoked_at'];

    protected $hidden = ['token_hash'];

    protected $casts = ['expires_at' => 'datetime', 'revoked_at' => 'datetime'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function guests(): HasMany
    {
        return $this->hasMany(LiveSessionGuest::class, 'guest_link_id');
    }

    public function isUsable(): bool
    {
        return $this->revoked_at === null
            && $this->expires_at->isFuture()
            && ($this->max_uses === null || $this->uses_count < $this->max_uses);
    }
}
