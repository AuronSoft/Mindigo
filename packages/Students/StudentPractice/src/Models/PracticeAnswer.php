<?php

namespace Mindigo\StudentPractice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\QuestionBank\Models\Question;

class PracticeAnswer extends Model
{
    protected $table = 'student_practice_answers';

    protected $fillable = [
        'attempt_id',
        'question_id',
        'question_snapshot',
        'difficulty_snapshot',
        'student_answer',
        'is_correct',
        'points',
        'response_seconds',
        'answer_revision',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'student_answer' => 'array',
            'question_snapshot' => 'array',
            'is_correct' => 'boolean',
            'points' => 'float',
            'answered_at' => 'datetime',
        ];
    }

    /**
     * Bài luyện tập chứa câu trả lời này
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(PracticeAttempt::class, 'attempt_id');
    }

    /**
     * Câu hỏi từ QuestionBank
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    /**
     * Lấy câu hỏi với đầy đủ thông tin
     */
    public function getQuestionWithDetailAttribute(): Question
    {
        return $this->question()->firstOrFail();
    }

    public function getDisplayContentAttribute(): string
    {
        return (string) ($this->question?->content ?? data_get($this->question_snapshot, 'content', ''));
    }

    public function getDisplayTypeAttribute(): string
    {
        return (string) ($this->question?->type ?? data_get($this->question_snapshot, 'type', 'short_answer'));
    }

    public function getDisplayOptionsAttribute(): array
    {
        return $this->question?->options ?? data_get($this->question_snapshot, 'options', []);
    }

    public function getDisplayExplanationAttribute(): ?string
    {
        return $this->question?->explanation ?? data_get($this->question_snapshot, 'explanation');
    }
}
