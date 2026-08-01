<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class ExamAttemptAnswer extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'exam_question_id',
        'type',
        'answer',
        'is_correct',
        'points_awarded',
        'needs_review',
        'feedback',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'answer' => 'array',
            'is_correct' => 'boolean',
            'points_awarded' => 'decimal:2',
            'needs_review' => 'boolean',
            'graded_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
