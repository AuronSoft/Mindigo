<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreScenario extends Model
{
    protected $table = 'learning_score_scenarios';

    protected $fillable = ['user_id', 'title', 'combination_code', 'subject_scores', 'priority_score', 'bonus_score', 'total_score'];

    protected function casts(): array
    {
        return ['subject_scores' => 'array', 'priority_score' => 'float', 'bonus_score' => 'float', 'total_score' => 'float'];
    }
}
