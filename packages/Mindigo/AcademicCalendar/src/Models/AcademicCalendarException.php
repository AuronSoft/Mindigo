<?php

namespace Mindigo\AcademicCalendar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherCourse\Models\Course;

class AcademicCalendarException extends Model
{
    public const KIND_NO_CLASS = 'no_class';

    protected $fillable = ['course_id', 'classroom_id', 'created_by', 'exception_date', 'kind', 'title', 'reason'];

    protected function casts(): array
    {
        return ['exception_date' => 'date'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
