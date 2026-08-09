<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherClassroom\Models\Classroom;

class StudyPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'creator_id', 'classroom_id', 'subject_id', 'title', 'description',
        'start_date', 'end_date', 'status',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(StudyPlanTask::class)->orderBy('position')->orderBy('due_date');
    }
}
