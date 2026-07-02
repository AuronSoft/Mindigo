<?php

namespace Database\Factories;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Mindigo\ExamManagement\Models\Exam;

class ExamFactory extends Factory
{
    protected $model = Exam::class;

    protected static ?Generator $fakerGenerator = null;

    private function generator(): Generator
    {
        return static::$fakerGenerator ??= FakerFactory::create();
    }

    public function definition(): array
    {
        $faker = $this->generator();

        return [
            'created_by'       => null,
            'title'            => $faker->sentence(4),
            'slug'             => $faker->unique()->slug(3),
            'subject'          => $faker->randomElement(['Toán', 'Văn', 'Anh', 'Lý', 'Hóa', 'Sinh']),
            'topic'            => $faker->words(3, true),
            'status'           => 'published',
            'description'      => $faker->sentence(),
            'duration_minutes' => $faker->randomElement([30, 45, 60, 90]),
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
