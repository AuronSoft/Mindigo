<?php

namespace Mindigo\ExamManagement\Models;

use Database\Factories\ExamAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mindigo\Auth\Models\User;

class ExamAttempt extends Model
{
    use HasFactory;

    protected static function newFactory(): ExamAttemptFactory
    {
        return ExamAttemptFactory::new();
    }

    public const STATUSES = ['in_progress', 'submitted', 'expired'];

    protected $fillable = [
        'exam_id',
        'user_id',
        'status',
        'started_at',
        'expires_at',
        'submitted_at',
        'graded_by',
        'graded_at',
        'score',
        'max_score',
        'percentage',
        'passed',
        'tab_leave_count',
        'question_order',
        'autosave_payload',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'percentage' => 'decimal:2',
            'passed' => 'boolean',
            'question_order' => 'array',
            'autosave_payload' => 'array',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function hasPendingReview(): bool
    {
        return $this->answers()->where('needs_review', true)->exists();
    }
}
