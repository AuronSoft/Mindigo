<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mindigo\ExamManagement\Models\ExamAttempt;

class ExamAttemptFactory extends Factory
{
    protected $model = ExamAttempt::class;

    public function definition(): array
    {
        $percentage = random_int(0, 10000) / 100;

        return [
            'exam_id'         => null,
            'user_id'         => null,
            'status'          => 'submitted',
            'started_at'      => now()->subHour(),
            'expires_at'      => now()->addHour(),
            'submitted_at'    => now(),
            'score'           => $percentage / 10,
            'max_score'       => 10.0,
            'percentage'      => $percentage,
            'passed'          => $percentage >= 50,
            'tab_leave_count' => 0,
            'question_order'  => null,
            'autosave_payload'=> null,
        ];
    }
}
