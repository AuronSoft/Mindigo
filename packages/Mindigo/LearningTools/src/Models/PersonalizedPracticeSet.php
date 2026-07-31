<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\QuestionBank\Models\Question;

class PersonalizedPracticeSet extends Model
{
    use SoftDeletes;

    protected $table = 'learning_practice_sets';

    protected $fillable = ['creator_id', 'classroom_id', 'title', 'subject', 'topic', 'difficulty', 'source'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'learning_practice_set_questions', 'learning_practice_set_id', 'question_id')
            ->withPivot('position')->orderByPivot('position');
    }
}
