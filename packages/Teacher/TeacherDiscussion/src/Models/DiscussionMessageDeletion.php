<?php

namespace Mindigo\TeacherDiscussion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class DiscussionMessageDeletion extends Model
{
    protected $table = 'teacher_discussion_message_deletions';

    protected $fillable = [
        'message_id',
        'user_id',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public $timestamps = false;

    public function message(): BelongsTo
    {
        return $this->belongsTo(DiscussionMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
