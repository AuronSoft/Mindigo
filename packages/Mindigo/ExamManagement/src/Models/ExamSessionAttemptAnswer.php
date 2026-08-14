<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mindigo\Auth\Models\User;

class ExamSessionAttemptAnswer extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['answer' => 'array', 'rubric_scores' => 'array', 'is_correct' => 'boolean', 'points_awarded' => 'decimal:2', 'needs_review' => 'boolean', 'reviewed_at' => 'datetime'];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamSessionAttempt::class, 'exam_session_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamTemplateQuestion::class, 'exam_template_question_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ExamGradeRevision::class);
    }
}
