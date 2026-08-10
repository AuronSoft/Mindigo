<?php

namespace Mindigo\TeacherClassroom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Lesson;

class ClassroomSchedule extends Model
{
    public const TYPE_REGULAR = 'regular';

    public const TYPE_MAKEUP = 'makeup';

    public const TYPES = [self::TYPE_REGULAR, self::TYPE_MAKEUP];

    public const DELIVERY_OFFLINE = 'offline';

    public const DELIVERY_ONLINE = 'online';

    public const DELIVERY_HYBRID = 'hybrid';

    public const DELIVERY_MODES = [self::DELIVERY_OFFLINE, self::DELIVERY_ONLINE, self::DELIVERY_HYBRID];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_RESCHEDULED = 'rescheduled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SCHEDULED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_RESCHEDULED,
    ];

    protected $table = 'classroom_schedules';

    protected $fillable = [
        'classroom_id',
        'lesson_id',
        'type',
        'delivery_mode',
        'status',
        'title',
        'session_date',
        'start_time',
        'end_time',
        'location',
        'meeting_url',
        'description',
        'makeup_reason',
        'cancel_reason',
        'substitute_teacher_id',
        'makeup_for_schedule_id',
        'rescheduled_from_id',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'session_date' => 'date',
        'published_at' => 'datetime',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function substituteTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'substitute_teacher_id');
    }

    public function makeupFor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'makeup_for_schedule_id');
    }

    public function rescheduledFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'rescheduled_from_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
