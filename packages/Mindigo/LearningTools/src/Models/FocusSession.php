<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;

class FocusSession extends Model
{
    protected $table = 'learning_focus_sessions';

    protected $fillable = [
        'user_id', 'subject_id', 'planned_minutes', 'focus_minutes',
        'break_minutes', 'started_at', 'ended_at', 'status',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
