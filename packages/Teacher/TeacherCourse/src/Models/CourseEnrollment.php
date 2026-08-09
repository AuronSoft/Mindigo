<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;

class CourseEnrollment extends Model
{
    public const STATUS_INVITED = 'invited';

    public const STATUS_ENROLLED = 'enrolled';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_WITHDRAWN = 'withdrawn';

    public const STATUSES = [self::STATUS_INVITED, self::STATUS_ENROLLED, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED, self::STATUS_WITHDRAWN];

    public const ACTIVE_STATUSES = [self::STATUS_INVITED, self::STATUS_ENROLLED, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED];

    protected $fillable = [
        'course_id', 'student_id', 'classroom_id', 'distribution_id', 'assigned_by', 'last_lesson_id', 'status', 'source',
        'completion_percentage', 'time_spent_seconds', 'invited_at', 'enrolled_at', 'started_at',
        'completed_at', 'withdrawn_at', 'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'completion_percentage' => 'integer', 'time_spent_seconds' => 'integer', 'invited_at' => 'datetime',
            'enrolled_at' => 'datetime', 'started_at' => 'datetime', 'completed_at' => 'datetime',
            'withdrawn_at' => 'datetime', 'last_activity_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class)->withTrashed();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(CourseClassroomAssignment::class, 'distribution_id');
    }

    public function scopeAvailableToStudent(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('source', 'self')
                ->orWhereHas('distribution', fn (Builder $assignment) => $assignment
                    ->where('visibility', 'visible')
                    ->where(fn (Builder $dates) => $dates->whereNull('starts_at')->orWhere('starts_at', '<=', now())));
        });
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function lastLesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'last_lesson_id');
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(CourseLessonProgress::class, 'enrollment_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(CourseReview::class, 'enrollment_id');
    }
}
