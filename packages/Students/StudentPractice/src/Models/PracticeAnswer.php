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
        'student_answer',
        'is_correct',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'student_answer' => 'array',
            'is_correct' => 'boolean',
            'points' => 'float',
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
}
