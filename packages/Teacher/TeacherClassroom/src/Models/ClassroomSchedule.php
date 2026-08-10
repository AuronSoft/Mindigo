<?php

namespace Mindigo\TeacherClassroom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomSchedule extends Model
{
    public const TYPE_REGULAR = 'regular';

    public const TYPE_MAKEUP = 'makeup';

    public const TYPES = [self::TYPE_REGULAR, self::TYPE_MAKEUP];

    protected $table = 'classroom_schedules';

    protected $fillable = [
        'classroom_id',
        'type',
        'title',
        'session_date',
        'start_time',
        'end_time',
        'description',
        'makeup_reason',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }
}
