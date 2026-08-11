<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;

final class LiveSessionRoomEvent extends Model
{
    protected $fillable = ['live_session_id', 'actor_id', 'target_user_id', 'type', 'payload', 'expires_at'];

    protected $casts = ['payload' => 'array', 'expires_at' => 'datetime'];
}
