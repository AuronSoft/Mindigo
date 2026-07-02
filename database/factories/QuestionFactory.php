<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mindigo\QuestionBank\Models\Question;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    protected static int $sequence = 0;

    public function definition(): array
    {
        $sequence = ++static::$sequence;
        $subject = $this->randomElement(['Toán', 'Văn', 'Anh', 'Lý', 'Hóa']);

        return [
            'created_by'     => null,
            'reviewed_by'    => null,
            'folder_id'      => null,
            'subject'        => $subject,
            'topic'          => 'Chủ đề ' . $sequence,
            'type'           => 'single_choice',
            'difficulty'     => $this->randomElement(['easy', 'medium', 'hard']),
            'status'         => 'approved',
            'content'        => 'Câu hỏi demo ' . $sequence . ' cho môn ' . $subject . '?',
            'options'        => [
                ['key' => 'A', 'text' => 'Đáp án A'],
                ['key' => 'B', 'text' => 'Đáp án B'],
                ['key' => 'C', 'text' => 'Đáp án C'],
                ['key' => 'D', 'text' => 'Đáp án D'],
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

    private function randomElement(array $items): mixed
    {
        return $items[array_rand($items)];
    }
}
