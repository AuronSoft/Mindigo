<?php

namespace Mindigo\TeacherAnnouncement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;

class Announcement extends Model
{
    use SoftDeletes;

    protected $table = 'teacher_announcements';

    public const TYPES = ['info', 'warning', 'reminder', 'assignment'];

    protected $fillable = [
        'teacher_id',
        'title',
        'content',
        'type',
        'is_pinned',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'announcement_classroom');
    }

    public function isDraft(): bool
    {
        return $this->published_at === null;
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}
