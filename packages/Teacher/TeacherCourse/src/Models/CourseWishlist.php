<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class CourseWishlist extends Model
{
    protected $fillable = ['user_id', 'course_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
