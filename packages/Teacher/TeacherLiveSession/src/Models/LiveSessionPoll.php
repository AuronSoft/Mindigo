<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class LiveSessionPoll extends Model
{
    protected $fillable = ['live_session_id', 'created_by', 'question', 'status', 'show_results', 'closed_at'];

    protected $casts = ['show_results' => 'boolean', 'closed_at' => 'datetime'];

    public function options(): HasMany
    {
        return $this->hasMany(LiveSessionPollOption::class, 'poll_id')->orderBy('position');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(LiveSessionPollVote::class, 'poll_id');
    }
}
