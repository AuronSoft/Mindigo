<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;

class Course extends Model
{
    use SoftDeletes;

    public const PUBLICATION_DRAFT = 'draft';

    public const PUBLICATION_PENDING_REVIEW = 'pending_review';

    public const PUBLICATION_PUBLISHED = 'published';

    public const PUBLICATION_UNLISTED = 'unlisted';

    public const PUBLICATION_ARCHIVED = 'archived';

    public const PUBLICATION_STATUSES = [
        self::PUBLICATION_DRAFT,
        self::PUBLICATION_PENDING_REVIEW,
        self::PUBLICATION_PUBLISHED,
        self::PUBLICATION_UNLISTED,
        self::PUBLICATION_ARCHIVED,
    ];

    public const DIFFICULTIES = ['beginner', 'intermediate', 'advanced'];

    public const EDUCATION_LEVELS = ['primary', 'lower_secondary', 'upper_secondary', 'university', 'general'];

    public const ACCESS_TYPES = ['free', 'paid'];

    public const DURATION_UNITS = ['minute', 'hour', 'session', 'day', 'week'];

    protected $table = 'courses';

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'category_id',
        'name',
        'slug',
        'description',
        'cover_image',
        'status',
        'publication_status',
        'is_active',
        'education_level',
        'difficulty',
        'language',
        'estimated_duration_minutes',
        'duration_value',
        'duration_unit',
        'learning_outcomes',
        'requirements',
        'target_learners',
        'submitted_for_review_at',
        'published_at',
        'published_by',
        'access_type',
        'price',
        'currency',
        'starts_at',
        'schedule_days',
        'study_time',
        'view_count',
        'enrollment_count',
        'rating_average',
        'rating_count',
        'is_featured',
        'featured_order',
        'featured_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'estimated_duration_minutes' => 'integer',
            'duration_value' => 'decimal:2',
            'learning_outcomes' => 'array',
            'requirements' => 'array',
            'target_learners' => 'array',
            'submitted_for_review_at' => 'datetime',
            'published_at' => 'datetime',
            'price' => 'decimal:2',
            'starts_at' => 'date',
            'schedule_days' => 'array',
            'view_count' => 'integer',
            'enrollment_count' => 'integer',
            'rating_average' => 'float',
            'rating_count' => 'integer',
            'is_featured' => 'boolean',
            'featured_order' => 'integer',
            'featured_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'course_id')->orderBy('sort_order');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Chapter::class, 'course_id', 'chapter_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function classroomAssignments(): HasMany
    {
        return $this->hasMany(CourseClassroomAssignment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }

    public function reviewHistory(): HasMany
    {
        return $this->hasMany(CourseReviewHistory::class)->latest();
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(CourseWishlist::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(CourseView::class);
    }

    public function isPublished(): bool
    {
        return $this->publication_status === self::PUBLICATION_PUBLISHED && $this->is_active;
    }

    public function durationLabel(): string
    {
        if ($this->duration_value && in_array($this->duration_unit, self::DURATION_UNITS, true)) {
            $value = rtrim(rtrim(number_format((float) $this->duration_value, 2, '.', ''), '0'), '.');

            return __('teacher-course::catalog.duration_units.'.$this->duration_unit, ['count' => $value]);
        }

        return $this->estimated_duration_minutes
            ? __('teacher-course::catalog.duration_units.minute', ['count' => $this->estimated_duration_minutes])
            : '—';
    }

    public function scopePubliclyListed(Builder $query): Builder
    {
        return $query
            ->where('publication_status', self::PUBLICATION_PUBLISHED)
            ->where('is_active', true);
    }
}
