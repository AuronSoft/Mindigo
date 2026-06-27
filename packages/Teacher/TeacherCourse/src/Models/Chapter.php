<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $table = 'chapters';

    protected $fillable = [
        'course_id',
        'name',
        'sort_order',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'chapter_id')->orderBy('sort_order');
    }
}
