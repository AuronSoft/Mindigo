<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;

final class LiveSessionGuestSignal extends Model
{
    protected $fillable = ['live_session_id', 'sender_key', 'recipient_key', 'type', 'payload', 'consumed_at'];

    protected $casts = ['payload' => 'array', 'consumed_at' => 'datetime'];
}
