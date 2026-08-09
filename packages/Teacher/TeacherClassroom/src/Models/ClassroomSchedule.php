<?php

namespace Mindigo\TeacherClassroom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomSchedule extends Model
{
    protected $table = 'classroom_schedules';

    protected $fillable = [
        'classroom_id',
        'title',
        'session_date',
        'start_time',
        'end_time',
        'description',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }
}
