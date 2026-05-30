<?php

namespace Mindigo\QuestionBank\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'correct_answers' => 'array',
            'tags' => 'array',
            'reviewed_at' => 'datetime',
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
}
