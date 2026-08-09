<?php

namespace Mindigo\TeacherClassroom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class ClassroomAttendance extends Model
{
    protected $table = 'classroom_attendances';

    protected $fillable = [
        'classroom_id',
        'student_id',
        'session_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
