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
            'title'            => $this->faker->sentence(4),
            'slug'             => $this->faker->unique()->slug(3),
            'subject'          => $this->faker->randomElement(['Toán', 'Văn', 'Anh', 'Lý', 'Hóa', 'Sinh']),
            'topic'            => $this->faker->words(3, true),
            'status'           => 'published',
            'description'      => $this->faker->sentence(),
            'duration_minutes' => $this->faker->randomElement([30, 45, 60, 90]),
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
