<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;

class Course extends Model
{
    use SoftDeletes;

    protected $table = 'courses';

    protected $fillable = [
        'teacher_id',
        'name',
        'slug',
        'description',
        'cover_image',
        'status',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'course_id')->orderBy('sort_order');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Chapter::class, 'course_id', 'chapter_id');
    }
}
