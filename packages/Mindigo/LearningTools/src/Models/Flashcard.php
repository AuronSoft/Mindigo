<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mindigo\Auth\Models\User;

class Flashcard extends Model
{
    protected $fillable = ['flashcard_deck_id', 'front', 'back', 'position'];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(FlashcardDeck::class, 'flashcard_deck_id');
    }

    public function learners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'flashcard_progress')
            ->withPivot(['rating', 'repetitions', 'interval_days', 'last_reviewed_at', 'next_review_at'])
            ->withTimestamps();
    }
}
