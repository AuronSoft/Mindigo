<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mindigo\TeacherAssignment\Models\Assignment;

class Lesson extends Model
{
    protected $table = 'lessons';

    protected $fillable = [
        'chapter_id',
        'name',
        'description',
        'is_preview',
        'content',
        'video_path',
        'attachment_paths',
        'assignment_id',
        'prerequisite_lesson_id',
        'sort_order',
    ];

    protected $casts = [
        'attachment_paths' => 'array',
        'is_preview' => 'boolean',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'prerequisite_lesson_id');
    }

    public function course(): Course
    {
        return $this->chapter->course;
    }

    public function learningProgress(): HasMany
    {
        return $this->hasMany(CourseLessonProgress::class);
    }
}
