<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mindigo\Auth\Models\User;

class ExamAssignment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
