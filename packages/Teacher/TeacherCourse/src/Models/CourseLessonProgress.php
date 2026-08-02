<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseLessonProgress extends Model
{
    protected $table = 'course_lesson_progress';

    protected $fillable = ['enrollment_id', 'lesson_id', 'time_spent_seconds', 'first_viewed_at', 'last_viewed_at', 'completed_at'];

    protected function casts(): array
    {
        return ['time_spent_seconds' => 'integer', 'first_viewed_at' => 'datetime', 'last_viewed_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
