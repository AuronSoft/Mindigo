<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\QuestionBank\Models\Question;

class ExamQuestion extends Model
{
    use SoftDeletes;

    public const TYPES = ['single_choice', 'multiple_choice', 'true_false', 'short_answer', 'essay'];

    protected $fillable = [
        'exam_id',
        'question_id',
        'sort_order',
        'subject',
        'topic',
        'type',
        'difficulty',
        'content',
        'options',
        'correct_answers',
        'explanation',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'correct_answers' => 'array',
            'points' => 'decimal:2',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function sourceQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
