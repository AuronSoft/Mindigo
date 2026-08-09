<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\SubjectManagement\Models\Subject;

class FlashcardDeck extends Model
{
    use SoftDeletes;

    protected $fillable = ['owner_id', 'subject_id', 'title', 'description', 'visibility'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Flashcard::class)->orderBy('position')->orderBy('id');
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'flashcard_deck_classroom')
            ->withPivot(['assigned_by', 'assigned_at'])->withTimestamps();
    }
}
