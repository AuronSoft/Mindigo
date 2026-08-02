<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class CourseReview extends Model
{
    public const STATUS_VISIBLE = 'visible';

    public const STATUS_HIDDEN = 'hidden';

    public const MODERATION_STATUSES = [self::STATUS_VISIBLE, self::STATUS_HIDDEN];

    protected $fillable = [
        'course_id', 'enrollment_id', 'student_id', 'rating', 'comment', 'moderation_status',
        'moderation_reason', 'moderated_by', 'moderated_at', 'teacher_reply', 'replied_by', 'replied_at',
    ];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'moderated_at' => 'datetime', 'replied_at' => 'datetime'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class, 'enrollment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function replier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('moderation_status', self::STATUS_VISIBLE);
    }
}
