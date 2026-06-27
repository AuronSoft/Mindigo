<?php

namespace Mindigo\TeacherDiscussion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;

class DiscussionThread extends Model
{
    use SoftDeletes;

    protected $table = 'teacher_discussion_threads';

    protected $fillable = [
        'teacher_id',
        'classroom_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DiscussionMessage::class, 'thread_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(DiscussionMessage::class, 'thread_id')->latestOfMany();
    }
}
