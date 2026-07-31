<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;

class GpaScenario extends Model
{
    protected $table = 'learning_gpa_scenarios';

    protected $fillable = ['user_id', 'title', 'courses', 'total_credits', 'average_ten', 'gpa_four', 'classification'];

    protected function casts(): array
    {
        return ['courses' => 'array', 'average_ten' => 'float', 'gpa_four' => 'float'];
    }
}
