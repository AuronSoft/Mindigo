<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class ExamGradeRevision extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['previous_rubric_scores' => 'array', 'new_rubric_scores' => 'array', 'previous_points' => 'decimal:2', 'new_points' => 'decimal:2'];
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(ExamSessionAttemptAnswer::class, 'exam_session_attempt_answer_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
