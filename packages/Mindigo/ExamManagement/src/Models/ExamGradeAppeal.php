<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamGradeAppeal extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_UPHELD = 'upheld';

    public const STATUS_REJECTED = 'rejected';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamSessionAttempt::class, 'exam_session_attempt_id');
    }
}
