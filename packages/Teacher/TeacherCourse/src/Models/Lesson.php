<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\TeacherAssignment\Models\Assignment;

class Lesson extends Model
{
    protected $table = 'lessons';

    protected $fillable = [
        'chapter_id',
        'name',
        'description',
        'content',
        'video_path',
        'attachment_paths',
        'assignment_id',
        'prerequisite_lesson_id',
        'sort_order',
    ];

    protected $casts = [
        'attachment_paths' => 'array',
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
}
