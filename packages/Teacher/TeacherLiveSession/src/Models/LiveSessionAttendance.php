<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Mindigo\Auth\Models\User;

class LiveSessionAttendance extends Model
{
    protected $table = 'live_session_attendances';

    protected $fillable = [
        'live_session_id',
        'user_id',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
