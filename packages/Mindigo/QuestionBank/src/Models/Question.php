<?php

namespace Mindigo\QuestionBank\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory(): QuestionFactory
    {
        return QuestionFactory::new();
    }

    public const TYPES = ['single_choice', 'multiple_choice', 'true_false', 'short_answer', 'essay'];

    public const DIFFICULTIES = ['easy', 'medium', 'hard'];

    public const STATUSES = ['draft', 'reviewing', 'approved', 'rejected'];

    public const PRACTICE_READY = 'ready';

    public const PRACTICE_NEEDS_REVIEW = 'needs_review';

    public const PRACTICE_DISABLED = 'disabled';

    protected $table = 'question_bank_questions';

    protected $fillable = [
        'created_by',
        'reviewed_by',
        'folder_id',
        'subject',
        'topic',
        'type',
        'difficulty',
        'status',
        'content',
        'options',
        'correct_answers',
        'explanation',
        'tags',
        'review_note',
        'reviewed_at',
        'subject_id',
        'subject_topic_id',
        'grade_level',
        'estimated_seconds',
        'hint',
        'practice_status',
        'readiness_issues',
        'practice_ready_at',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'correct_answers' => 'array',
            'tags' => 'array',
            'reviewed_at' => 'datetime',
            'readiness_issues' => 'array',
            'practice_ready_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(QuestionFolder::class, 'folder_id');
    }

    public function scopePracticeReady(Builder $query): Builder
    {
        return $query->where('status', 'approved')
            ->where('practice_status', self::PRACTICE_READY);
    }

    public function editHistories(): HasMany
    {
        return $this->hasMany(QuestionEditHistory::class, 'question_id')->latest();
    }
}
