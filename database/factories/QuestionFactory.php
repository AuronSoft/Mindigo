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
            'subject'        => $this->faker->randomElement(['Toán', 'Văn', 'Anh', 'Lý', 'Hóa']),
            'topic'          => $this->faker->words(2, true),
            'type'           => 'single_choice',
            'difficulty'     => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'status'         => 'approved',
            'content'        => $this->faker->sentence() . '?',
            'options'        => [
                ['key' => 'A', 'text' => $this->faker->word()],
                ['key' => 'B', 'text' => $this->faker->word()],
                ['key' => 'C', 'text' => $this->faker->word()],
                ['key' => 'D', 'text' => $this->faker->word()],
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
