<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class CourseView extends Model
{
    protected $fillable = ['user_id', 'course_id', 'view_count', 'last_viewed_at'];

    protected function casts(): array
    {
        return ['view_count' => 'integer', 'last_viewed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
