<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;

class Exam extends Model
{
    use SoftDeletes;

    public const STATUSES = ['draft', 'reviewing', 'published', 'closed'];

    protected $fillable = [
        'created_by',
        'title',
        'slug',
        'subject',
        'topic',
        'status',
        'description',
        'duration_minutes',
        'starts_at',
        'ends_at',
        'max_attempts',
        'passing_score',
        'shuffle_questions',
        'shuffle_answers',
        'show_results',
        'audience',
        'generation_config',
        'total_questions',
        'total_points',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
            'shuffle_questions' => 'boolean',
            'shuffle_answers' => 'boolean',
            'show_results' => 'boolean',
            'audience' => 'array',
            'generation_config' => 'array',
            'total_points' => 'decimal:2',
            'passing_score' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        $now = now();

        return (!$this->starts_at || $this->starts_at->lte($now))
            && (!$this->ends_at || $this->ends_at->gte($now));
    }
}
