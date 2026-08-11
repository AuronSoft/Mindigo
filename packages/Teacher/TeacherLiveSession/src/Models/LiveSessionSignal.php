<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;

final class LiveSessionSignal extends Model
{
    protected $fillable = ['live_session_id', 'sender_id', 'recipient_id', 'type', 'payload', 'consumed_at'];

    protected $casts = ['payload' => 'array', 'consumed_at' => 'datetime'];
}
