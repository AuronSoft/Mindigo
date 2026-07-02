<?php

namespace Database\Factories;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Mindigo\QuestionBank\Models\Question;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    protected static ?Generator $fakerGenerator = null;

    private function generator(): Generator
    {
        return static::$fakerGenerator ??= FakerFactory::create();
    }

    public function definition(): array
    {
        $faker = $this->generator();

        return [
            'created_by'     => null,
            'reviewed_by'    => null,
            'folder_id'      => null,
            'subject'        => $faker->randomElement(['Toán', 'Văn', 'Anh', 'Lý', 'Hóa']),
            'topic'          => $faker->words(2, true),
            'type'           => 'single_choice',
            'difficulty'     => $faker->randomElement(['easy', 'medium', 'hard']),
            'status'         => 'approved',
            'content'        => $faker->sentence() . '?',
            'options'        => [
                ['key' => 'A', 'text' => $faker->word()],
                ['key' => 'B', 'text' => $faker->word()],
                ['key' => 'C', 'text' => $faker->word()],
                ['key' => 'D', 'text' => $faker->word()],
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
