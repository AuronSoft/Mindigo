<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicScoreScenario extends Model
{
    protected $table = 'learning_academic_score_scenarios';

    protected $fillable = ['user_id', 'title', 'type', 'items', 'bonus_score', 'result'];

    protected function casts(): array
    {
        return ['items' => 'array', 'bonus_score' => 'float', 'result' => 'float'];
    }
}
