<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;

final class LiveProviderConnection extends Model
{
    protected $fillable = ['user_id', 'provider', 'access_token', 'refresh_token', 'scopes', 'external_account_id', 'external_email', 'expires_at', 'revoked_at', 'last_refreshed_at'];

    protected $casts = [
        'provider' => LiveSessionProvider::class,
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'scopes' => 'array',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_refreshed_at' => 'datetime',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUsable(): bool
    {
        return $this->revoked_at === null && filled($this->access_token);
    }
}
