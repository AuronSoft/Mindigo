<?php

namespace Mindigo\TeacherDiscussion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;

class DiscussionThread extends Model
{
    use SoftDeletes;

    public const TYPE_CLASS = 'class';

    public const TYPE_DIRECT = 'direct';

    public const TYPE_GROUP = 'group';

    public const TYPES = [self::TYPE_CLASS, self::TYPE_DIRECT, self::TYPE_GROUP];

    protected $table = 'teacher_discussion_threads';

    protected $fillable = [
        'teacher_id',
        'classroom_id',
        'type',
        'name',
        'avatar',
        'theme_color',
        'description',
        'created_by',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(DiscussionParticipant::class, 'thread_id');
    }

    public function participantFor(int $userId): ?DiscussionParticipant
    {
        return $this->participants->firstWhere('user_id', $userId);
    }

    public function isParticipant(int $userId): bool
    {
        return $this->participants->contains('user_id', $userId);
    }

    public function lastReadFor(int $userId): ?Carbon
    {
        return $this->participantFor($userId)?->last_read_at;
    }

    public function unreadCountFor(int $userId): int
    {
        if (isset($this->attributes['unread_messages_count'])) {
            return (int) $this->attributes['unread_messages_count'];
        }

        $lastRead = $this->lastReadFor($userId);

        return $this->messages()
            ->when($lastRead, fn ($query) => $query->where('created_at', '>', $lastRead))
            ->where('sender_id', '!=', $userId)
            ->count();
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
