<?php

namespace Mindigo\StudentPractice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;

class PracticeAttempt extends Model
{
    protected $table = 'student_practice_attempts';

    protected $fillable = [
        'student_id',
        'mode', // 'subject', 'topic', 'mixed'
        'subject',
        'topic',
        'difficulty', // null = all, or specific level
        'total_questions',
        'correct_answers',
        'score', // percentage
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'score' => 'float',
        ];
    }

    /**
     * Học sinh thực hiện bài luyện tập
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Các câu trả lời của học sinh
     */
    public function answers(): HasMany
    {
        return $this->hasMany(PracticeAnswer::class, 'attempt_id');
    }

    /**
     * Tính điểm dựa trên số câu trả lời đúng
     */
    public function calculateScore(): float
    {
        if ($this->total_questions === 0) {
            return 0;
        }

        return round(($this->correct_answers / $this->total_questions) * 100, 2);
    }

    /**
     * Đánh dấu bài luyện tập hoàn thành
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
            'score' => $this->calculateScore(),
        ]);
    }

    /**
     * Kiểm tra xem bài luyện tập đã hoàn thành chưa
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Lấy thời gian thực hiện (phút)
     */
    public function getDurationInMinutesAttribute(): int|null
    {
        if (!$this->isCompleted()) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }
}
