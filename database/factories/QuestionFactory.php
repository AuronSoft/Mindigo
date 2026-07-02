<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mindigo\QuestionBank\Models\Question;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'created_by'     => null,
            'reviewed_by'    => null,
            'folder_id'      => null,
            'subject'        => \fake()->randomElement(['Toán', 'Văn', 'Anh', 'Lý', 'Hóa']),
            'topic'          => \fake()->words(2, true),
            'type'           => 'single_choice',
            'difficulty'     => \fake()->randomElement(['easy', 'medium', 'hard']),
            'status'         => 'approved',
            'content'        => \fake()->sentence() . '?',
            'options'        => [
                ['key' => 'A', 'text' => \fake()->word()],
                ['key' => 'B', 'text' => \fake()->word()],
                ['key' => 'C', 'text' => \fake()->word()],
                ['key' => 'D', 'text' => \fake()->word()],
            ],
            'correct_answers'=> ['A'],
            'explanation'    => null,
            'tags'           => null,
            'review_note'    => null,
            'reviewed_at'    => null,
        ];
    }

    public function reviewing(): static
    {
        return $this->state(['status' => 'reviewing', 'reviewed_at' => null]);
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }
}
