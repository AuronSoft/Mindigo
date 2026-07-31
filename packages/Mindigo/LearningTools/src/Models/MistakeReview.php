<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;

class MistakeReview extends Model
{
    protected $table = 'learning_mistake_reviews';

    protected $fillable = [
        'user_id', 'source_type', 'source_answer_id', 'note', 'is_resolved',
        'review_count', 'last_reviewed_at',
    ];

    protected function casts(): array
    {
        return ['is_resolved' => 'boolean', 'last_reviewed_at' => 'datetime'];
    }
}
