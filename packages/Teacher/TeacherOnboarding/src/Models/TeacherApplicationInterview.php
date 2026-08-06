<?php

namespace Mindigo\TeacherOnboarding\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class TeacherApplicationInterview extends Model
{
    public const MODE_ONLINE = 'online';

    public const MODE_OFFLINE = 'offline';

    public const MODES = [
        self::MODE_ONLINE,
        self::MODE_OFFLINE,
    ];

    public const RESULT_PASSED = 'passed';

    public const RESULT_FAILED = 'failed';

    public const RESULT_NEED_SECOND_INTERVIEW = 'need_second_interview';

    public const RESULTS = [
        self::RESULT_PASSED,
        self::RESULT_FAILED,
        self::RESULT_NEED_SECOND_INTERVIEW,
    ];

    protected $fillable = [
        'teacher_application_id',
        'interviewer_id',
        'scheduled_at',
        'mode',
        'meeting_url',
        'pre_interview_note',
        'subject_knowledge_score',
        'pedagogy_score',
        'communication_score',
        'lms_technology_score',
        'overall_comment',
        'result',
        'evaluated_at',
        'evaluated_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'evaluated_at' => 'datetime',
            'subject_knowledge_score' => 'integer',
            'pedagogy_score' => 'integer',
            'communication_score' => 'integer',
            'lms_technology_score' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(TeacherApplication::class, 'teacher_application_id');
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
