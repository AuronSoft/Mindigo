<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class ExamGradingAssignment extends Model
{
    protected $guarded = ['id'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'grader_id');
    }
}
