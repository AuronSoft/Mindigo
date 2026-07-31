<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mindigo\Auth\Models\User;

class StudyPlanTask extends Model
{
    protected $fillable = ['study_plan_id', 'title', 'description', 'due_date', 'position'];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(StudyPlan::class, 'study_plan_id');
    }

    public function completedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'study_task_completions')
            ->withPivot('completed_at')->withTimestamps();
    }
}
