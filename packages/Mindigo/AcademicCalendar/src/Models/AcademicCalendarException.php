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

    protected $fillable = ['scope_key', 'course_id', 'classroom_id', 'created_by', 'exception_date', 'kind', 'title', 'reason'];

    protected static function booted(): void
    {
        static::saving(function (self $exception): void {
            $scope = $exception->classroom_id ? "classroom:{$exception->classroom_id}" : ($exception->course_id ? "course:{$exception->course_id}" : 'global:0');
            $exception->scope_key = "{$scope}:{$exception->kind}:".$exception->exception_date->format('Y-m-d');
        });
    }

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
