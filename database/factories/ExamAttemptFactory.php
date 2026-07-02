<?php

namespace Database\Factories;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Mindigo\ExamManagement\Models\ExamAttempt;

class ExamAttemptFactory extends Factory
{
    protected $model = ExamAttempt::class;

    protected static ?Generator $fakerGenerator = null;

    private function generator(): Generator
    {
        return static::$fakerGenerator ??= FakerFactory::create();
    }

    public function definition(): array
    {
        $percentage = $this->generator()->randomFloat(2, 0, 100);

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
