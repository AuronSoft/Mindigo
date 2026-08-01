<?php

namespace Mindigo\StudentPractice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class PracticeLearningInsight extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUPERSEDED = 'superseded';

    protected $table = 'student_practice_insights';

    protected $fillable = [
        'student_id', 'practice_skill_id', 'fingerprint', 'type', 'insight_code', 'priority',
        'metrics', 'engine_version', 'status', 'period_start', 'period_end', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'generated_at' => 'datetime',
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
