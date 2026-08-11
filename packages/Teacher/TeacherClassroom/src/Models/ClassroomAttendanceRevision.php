<?php

namespace Mindigo\TeacherClassroom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class ClassroomAttendanceRevision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['attendance_id', 'changed_by', 'old_values', 'new_values', 'change_reason'];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array'];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(ClassroomAttendance::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
