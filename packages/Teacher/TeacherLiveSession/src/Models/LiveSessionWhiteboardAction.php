<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;

final class LiveSessionWhiteboardAction extends Model
{
    protected $fillable = ['live_session_id', 'actor_id', 'type', 'payload'];

    protected $casts = ['payload' => 'array'];
}
