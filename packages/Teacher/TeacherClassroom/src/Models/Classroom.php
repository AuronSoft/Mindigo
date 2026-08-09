<?php

namespace Mindigo\TeacherClassroom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherAnnouncement\Models\Announcement;

class Classroom extends Model
{
    use SoftDeletes;

    public const STATUSES = ['active', 'inactive'];

    protected $fillable = [
        'created_by',
        'teacher_id',
        'assistant_id',
        'name',
        'code',
        'slug',
        'school_year',
        'description',
        'status',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function studentLinks(): HasMany
    {
        return $this->hasMany(ClassroomStudent::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_students', 'classroom_id', 'student_id')
            ->withPivot(['status', 'joined_at'])
            ->withTimestamps();
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'classroom_subjects')
            ->withTimestamps();
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ClassroomAttendance::class, 'classroom_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassroomSchedule::class, 'classroom_id');
    }

    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(Announcement::class, 'announcement_classroom');
    }
}
