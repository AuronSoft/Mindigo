<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;

class CourseClassroomAssignment extends Model
{
    public const VISIBILITIES = ['visible', 'hidden'];

    protected $fillable = ['course_id', 'classroom_id', 'assigned_by', 'assigned_at', 'starts_at', 'due_at', 'is_mandatory', 'visibility'];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime', 'starts_at' => 'datetime', 'due_at' => 'datetime', 'is_mandatory' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
