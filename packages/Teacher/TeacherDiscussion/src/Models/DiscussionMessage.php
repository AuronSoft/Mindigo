<?php

namespace Mindigo\TeacherDiscussion\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;

class DiscussionMessage extends Model
{
    use SoftDeletes;

    protected $table = 'teacher_discussion_messages';

    protected $fillable = [
        'thread_id',
        'sender_id',
        'reply_to_id',
        'body',
        'edited_at',
        'read_at',
        'is_pinned',
        'pinned_at',
        'pinned_by',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'edited_at' => 'datetime',
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function repliesTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'reply_to_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(DiscussionMessageReaction::class, 'message_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(DiscussionMessageRead::class, 'message_id');
    }

    public function deletions(): HasMany
    {
        return $this->hasMany(DiscussionMessageDeletion::class, 'message_id');
    }

    /**
     * Có ít nhất một người khác (không phải người gửi) đã đọc tin nhắn chưa?
     */
    public function isReadByOthers(): bool
    {
        if ($this->relationLoaded('reads')) {
            return $this->reads
                ->reject(fn ($read) => (int) $read->user_id === (int) $this->sender_id)
                ->isNotEmpty();
        }

        return $this->reads()
            ->where('user_id', '!=', $this->sender_id)
            ->exists();
    }

    /**
     * Tin nhắn đã bị xoá chỉ cho một người dùng cụ thể chưa?
     */
    public function isDeletedFor(int $userId): bool
    {
        return $this->deletions()
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Phạm vi bỏ qua các tin nhắn đã bị xoá cho người dùng đang xem.
     */
    public function scopeNotDeletedFor(Builder $query, int $userId): Builder
    {
        return $query->whereDoesntHave('deletions', fn (Builder $q) => $q->where('user_id', $userId));
    }

    public function reactionSummary(): array
    {
        return $this->reactions
            ->groupBy('emoji')
            ->map(fn ($items, $emoji) => [
                'emoji' => $emoji,
                'count' => $items->count(),
            ])
            ->values()
            ->all();
    }

    public function pinnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DiscussionAttachment::class, 'message_id');
    }
}
