<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LiveProviderSubscription extends Model
{
    protected $fillable = ['connection_id', 'provider', 'channel_id', 'resource_id', 'resource_uri', 'expires_at', 'last_renewed_at', 'status', 'last_error'];

    protected $casts = ['expires_at' => 'datetime', 'last_renewed_at' => 'datetime'];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(LiveProviderConnection::class, 'connection_id');
    }
}
