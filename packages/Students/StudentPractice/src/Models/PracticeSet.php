<?php

namespace Mindigo\StudentPractice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\QuestionBank\Models\Question;

class PracticeSet extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_READY = 'ready';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [self::STATUS_DRAFT, self::STATUS_READY, self::STATUS_ARCHIVED];

    protected $table = 'learning_practice_sets';

    protected $fillable = [
        'creator_id',
        'classroom_id',
        'title',
        'subject',
        'topic',
        'difficulty',
        'source',
        'status',
    ];

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
        return $this->belongsToMany(
            Question::class,
            'learning_practice_set_questions',
            'learning_practice_set_id',
            'question_id'
        )->withPivot('position')->orderByPivot('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PracticeAttempt::class, 'practice_set_id');
    }
}
