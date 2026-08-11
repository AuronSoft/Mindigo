<?php

namespace Mindigo\TeacherClassroom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mindigo\Auth\Models\User;

class ClassroomAttendance extends Model
{
    protected $table = 'classroom_attendances';

    protected $fillable = [
        'classroom_id',
        'identity_key',
        'classroom_schedule_id',
        'student_id',
        'attendance_session_id',
        'session_date',
        'status',
        'late_minutes',
        'absence_reason',
        'method',
        'remarks',
        'updated_by',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $attendance): void {
            $attendance->identity_key = $attendance->classroom_schedule_id
                ? "schedule:{$attendance->classroom_schedule_id}:student:{$attendance->student_id}"
                : "classroom:{$attendance->classroom_id}:date:".$attendance->session_date->format('Y-m-d').":student:{$attendance->student_id}";
        });
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(ClassroomAttendanceSession::class, 'attendance_session_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassroomSchedule::class, 'classroom_schedule_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ClassroomAttendanceRevision::class, 'attendance_id')->latest('id');
    }
}
