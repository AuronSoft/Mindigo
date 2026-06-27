<?php

namespace Mindigo\TeacherAssignment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $table = 'assignment_submissions';

    protected $fillable = [
        'assignment_id',
        'student_id',
        // Hình thức 1
        'file_path',
        'file_original_name',
        // Hình thức 2
        'text_content',
        // Chung
        'submitted_at',
        'is_late',
        'score',
        'feedback',
        'graded_at',
        'status',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_late' => 'boolean',
        'score' => 'float',
    ];

    //Relationships

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(\Mindigo\Auth\Models\User::class, 'student_id');
    }

    //Helpers 

    public function isGraded(): bool
    {
        return in_array($this->status, ['graded', 'returned']);
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }

    public function hasText(): bool
    {
        return !empty($this->text_content);
    }

    public function scorePercent(): ?float
    {
        if (is_null($this->score))
            return null;
        $max = $this->assignment->max_score;
        return $max > 0 ? round(($this->score / $max) * 100, 1) : 0;
    }
}
