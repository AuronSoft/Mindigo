<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mindigo\ExamManagement\Models\Exam;

class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        return [
            'created_by'       => null,
            'title'            => \fake()->sentence(4),
            'slug'             => \fake()->unique()->slug(3),
            'subject'          => \fake()->randomElement(['Toán', 'Văn', 'Anh', 'Lý', 'Hóa', 'Sinh']),
            'topic'            => \fake()->words(3, true),
            'status'           => 'published',
            'description'      => \fake()->sentence(),
            'duration_minutes' => \fake()->randomElement([30, 45, 60, 90]),
            'starts_at'        => null,
            'ends_at'          => null,
            'max_attempts'     => 3,
            'passing_score'    => 5.0,
            'shuffle_questions'=> false,
            'shuffle_answers'  => false,
            'show_results'     => true,
            'audience'         => null,
            'generation_config'=> null,
            'total_questions'  => 10,
            'total_points'     => 10.0,
            'published_at'     => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft', 'published_at' => null]);
    }

    public function reviewing(): static
    {
        return $this->state(['status' => 'reviewing']);
    }
}
