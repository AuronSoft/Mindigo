<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class ExamCandidate extends Model
{
    public const STATUS_ELIGIBLE = 'eligible';

    public const STATUS_EXCLUDED = 'excluded';

    public const STATUSES = [self::STATUS_ELIGIBLE, self::STATUS_EXCLUDED];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
