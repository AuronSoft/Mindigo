<?php

namespace Mindigo\Profile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';

    protected $fillable = [
        'user_id',
        'notif_new_quiz',
        'notif_system_news',
        'notif_calendar_updates',
        'notif_calendar_reminders',
    ];

    protected $casts = [
        'notif_new_quiz' => 'boolean',
        'notif_system_news' => 'boolean',
        'notif_calendar_updates' => 'boolean',
        'notif_calendar_reminders' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
