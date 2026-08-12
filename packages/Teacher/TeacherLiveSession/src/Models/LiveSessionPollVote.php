<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;

final class LiveSessionPollVote extends Model
{
    protected $fillable = ['poll_id', 'option_id', 'user_id'];
}
