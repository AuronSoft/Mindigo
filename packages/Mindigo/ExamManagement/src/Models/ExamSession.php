<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;

class ExamSession extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_LIVE = 'live';

    public const STATUS_ENDED = 'ended';

    public const STATUS_GRADING = 'grading';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [self::STATUS_DRAFT, self::STATUS_SCHEDULED, self::STATUS_LIVE, self::STATUS_ENDED, self::STATUS_GRADING, self::STATUS_COMPLETED, self::STATUS_ARCHIVED];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'scheduled_at' => 'datetime', 'completed_at' => 'datetime', 'shuffle_questions' => 'boolean', 'shuffle_answers' => 'boolean', 'security_policy' => 'array', 'passing_score' => 'decimal:2'];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ExamTemplateVersion::class, 'exam_template_version_id');
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ExamAssignment::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(ExamCandidate::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamSessionAttempt::class);
    }

    public function isMutable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED], true);
    }
}
