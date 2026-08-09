<?php

namespace Mindigo\TeacherAssignment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assignments';

    protected $fillable = [
        'classroom_id',
        'teacher_id',
        'title',
        'description',
        'file_path',
        'due_date',
        'allow_late',
        'late_days',
        'max_score',
        'submission_type', // file | text | both
        'status',          // draft | published
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'allow_late' => 'boolean',
        'max_score' => 'integer',
        'late_days' => 'integer',
        'file_path' => 'array',
    ];

    // Relationships

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    // Scopes

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // Helpers

    public function isOverdue(): bool
    {
        return $this->due_date ? now()->gt($this->due_date) : false;
    }

    public function allowsFile(): bool
    {
        return in_array($this->submission_type, ['file', 'both']);
    }

    public function allowsText(): bool
    {
        return in_array($this->submission_type, ['text', 'both']);
    }

    public function submittedCount(): int
    {
        return $this->submissions()->count();
    }

    public function gradedCount(): int
    {
        return $this->submissions()->whereNotNull('score')->count();
    }
}
