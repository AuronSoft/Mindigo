<?php

namespace Mindigo\StudentPractice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mindigo\Auth\Models\User;

class PracticeAttempt extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ABANDONED = 'abandoned';

    public const STATUS_EXPIRED = 'expired';

    public const STATUSES = [
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_ABANDONED,
        self::STATUS_EXPIRED,
    ];

    protected $table = 'student_practice_attempts';

    protected $fillable = [
        'student_id',
        'practice_set_id',
        'practice_skill_id',
        'mode', // 'subject', 'topic', 'mixed'
        'subject',
        'topic',
        'difficulty', // null = all, or specific level
        'question_pool_size',
        'selection_strategy',
        'is_adaptive',
        'mastery_before',
        'mastery_after',
        'adaptive_context',
        'total_questions',
        'correct_answers',
        'score', // percentage
        'status',
        'started_at',
        'last_activity_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'completed_at' => 'datetime',
            'score' => 'float',
            'is_adaptive' => 'boolean',
            'mastery_before' => 'float',
            'mastery_after' => 'float',
            'adaptive_context' => 'array',
        ];
    }

    /**
     * Học sinh thực hiện bài luyện tập
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function practiceSet(): BelongsTo
    {
        return $this->belongsTo(PracticeSet::class, 'practice_set_id');
    }

    public function practiceSkill(): BelongsTo
    {
        return $this->belongsTo(PracticeSkill::class, 'practice_skill_id');
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
        return $this->status === self::STATUS_COMPLETED || $this->completed_at !== null;
    }

    /**
     * Lấy thời gian thực hiện (phút)
     */
    public function getDurationInMinutesAttribute(): ?int
    {
        if (! $this->isCompleted()) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }
}
