<?php

namespace Mindigo\StudentPractice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class StudentSkillProgress extends Model
{
    protected $table = 'student_skill_progress';

    protected $fillable = [
        'student_id', 'practice_skill_id', 'completed_attempts', 'total_questions',
        'correct_answers', 'accuracy', 'average_score', 'best_score',
        'mastery_score', 'mastery_level', 'confidence_score', 'recommended_difficulty',
        'consecutive_correct', 'consecutive_incorrect', 'engine_version',
        'practice_seconds', 'last_practiced_at', 'last_evaluated_at',
    ];

    protected function casts(): array
    {
        return [
            'accuracy' => 'float', 'average_score' => 'float', 'best_score' => 'float',
            'mastery_score' => 'float', 'confidence_score' => 'float',
            'last_practiced_at' => 'datetime', 'last_evaluated_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(PracticeSkill::class, 'practice_skill_id');
    }
}
