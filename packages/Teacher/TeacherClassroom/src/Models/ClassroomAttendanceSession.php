<?php

namespace Mindigo\TeacherClassroom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mindigo\Auth\Models\User;

class ClassroomAttendanceSession extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = ['classroom_id', 'classroom_schedule_id', 'opened_by', 'session_date', 'code', 'status', 'expires_at', 'late_after_minutes', 'closed_at'];

    protected function casts(): array
    {
        return ['session_date' => 'date', 'code' => 'encrypted', 'expires_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassroomSchedule::class, 'classroom_schedule_id');
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ClassroomAttendance::class, 'attendance_session_id');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN && $this->expires_at->isFuture();
    }
}
