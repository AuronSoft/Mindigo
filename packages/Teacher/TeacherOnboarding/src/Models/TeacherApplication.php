<?php

namespace Mindigo\TeacherOnboarding\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;

class TeacherApplication extends Model
{
    use SoftDeletes;

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_SCREENING = 'screening';

    public const STATUS_NEED_MORE_INFO = 'need_more_info';

    public const STATUS_INTERVIEW_SCHEDULED = 'interview_scheduled';

    public const STATUS_INTERVIEWED = 'interviewed';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SUSPENDED = 'suspended';

    public const ACTIVE_STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_SCREENING,
        self::STATUS_NEED_MORE_INFO,
        self::STATUS_INTERVIEW_SCHEDULED,
        self::STATUS_INTERVIEWED,
    ];

    public const APPLICATION_TYPES = ['teacher', 'tutor'];

    public const TEACHING_MODES = ['online', 'offline', 'hybrid'];

    protected $fillable = [
        'user_id',
        'application_code',
        'status',
        'application_type',
        'full_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'subject_id',
        'category_id',
        'education_level',
        'specialization',
        'teaching_skills',
        'teaching_mode',
        'experience_years',
        'current_organization',
        'previous_organizations',
        'achievements',
        'experience_description',
        'verification_documents',
        'teaching_method',
        'intro_video_url',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'teaching_skills' => 'array',
            'verification_documents' => 'array',
            'experience_years' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function scopeActiveReview(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }
}
