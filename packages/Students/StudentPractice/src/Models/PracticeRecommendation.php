<?php

namespace Mindigo\StudentPractice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class PracticeRecommendation extends Model
{
    public const TYPE_REVIEW = 'review';

    public const TYPE_CONTINUE = 'continue';

    public const TYPE_ADVANCE = 'advance';

    public const STATUS_ACTIVE = 'active';

    protected $table = 'student_practice_recommendations';

    protected $fillable = [
        'student_id', 'practice_skill_id', 'type', 'priority', 'target_difficulty',
        'reason_code', 'reason_context', 'engine_version', 'status', 'generated_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'reason_context' => 'array',
            'generated_at' => 'datetime',
            'expires_at' => 'datetime',
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
