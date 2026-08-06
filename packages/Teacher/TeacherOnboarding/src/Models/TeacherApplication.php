<?php

namespace Mindigo\TeacherOnboarding\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\CourseCategory;

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

    public const STATUS_REVOKED = 'revoked';

    public const PROVISION_NOT_PROVISIONED = 'not_provisioned';

    public const PROVISION_ACTIVE = 'active';

    public const PROVISION_SUSPENDED = 'suspended';

    public const PROVISION_REVOKED = 'revoked';

    public const PROVISION_STATUSES = [
        self::PROVISION_NOT_PROVISIONED,
        self::PROVISION_ACTIVE,
        self::PROVISION_SUSPENDED,
        self::PROVISION_REVOKED,
    ];

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
        'teacher_provision_status',
        'provisioned_user_role',
        'provisioned_by',
        'provisioned_at',
        'teacher_suspended_at',
        'teacher_revoked_at',
        'provisioning_note',
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
        'reviewed_by',
        'reviewed_at',
        'internal_note',
        'status_note',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'teaching_skills' => 'array',
            'verification_documents' => 'array',
            'experience_years' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'provisioned_at' => 'datetime',
            'teacher_suspended_at' => 'datetime',
            'teacher_revoked_at' => 'datetime',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function provisioner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provisioned_by');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(TeacherApplicationInterview::class);
    }

    public function latestInterview(): HasOne
    {
        return $this->hasOne(TeacherApplicationInterview::class)->latestOfMany();
    }

    public function scopeActiveReview(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }
}
