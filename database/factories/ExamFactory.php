<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Mindigo\ExamManagement\Models\Exam;

class ExamFactory extends Factory
{
    protected $model = Exam::class;

    protected static int $sequence = 0;

    public function definition(): array
    {
        $sequence = ++static::$sequence;
        $subject = $this->randomElement(['Toán', 'Văn', 'Anh', 'Lý', 'Hóa', 'Sinh']);
        $title = 'Đề thi demo '.$sequence.' môn '.$subject;

        return [
            'created_by' => null,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'subject' => $subject,
            'topic' => 'Chủ đề ôn tập '.$sequence,
            'status' => 'published',
            'description' => 'Đề thi demo dùng để kiểm tra hệ thống Mindigo.',
            'duration_minutes' => $this->randomElement([30, 45, 60, 90]),
            'starts_at' => null,
            'ends_at' => null,
            'max_attempts' => 3,
            'passing_score' => 5.0,
            'shuffle_questions' => false,
            'shuffle_answers' => false,
            'show_results' => true,
            'audience' => null,
            'generation_config' => null,
            'total_questions' => 10,
            'total_points' => 10.0,
            'published_at' => now(),
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

    private function randomElement(array $items): mixed
    {
        return $items[array_rand($items)];
    }
}
