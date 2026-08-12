<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;

final class LiveSessionPollOption extends Model
{
    public $timestamps = false;

    protected $fillable = ['poll_id', 'label', 'position'];
}
