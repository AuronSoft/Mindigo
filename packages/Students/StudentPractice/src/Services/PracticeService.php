<?php

namespace Mindigo\StudentPractice\Services;

use Mindigo\QuestionBank\Models\Question;
use Mindigo\QuestionBank\Services\QuestionBankService;

class PracticeService
{
    public function __construct(protected QuestionBankService $questionBank)
    {
    }

    /**
     * Lấy danh sách câu hỏi approved để luyện tập theo filter.
     */
    public function getQuestions(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Question::query()
            ->where('status', 'approved')
            ->latest('updated_at');

        foreach (['subject', 'topic', 'type', 'difficulty'] as $filter) {
            if (filled($filters[$filter] ?? null)) {
                $query->where($filter, $filters[$filter]);
            }
        }

        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($builder) use ($keyword) {
                $builder->where('content', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%")
                    ->orWhere('topic', 'like', "%{$keyword}%");
            });
        }

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Lấy một câu hỏi theo ID (chỉ approved).
     */
    public function getQuestion(int $id): ?Question
    {
        return Question::query()
            ->where('status', 'approved')
            ->find($id);
    }

    /**
     * Dữ liệu cho form filter / bắt đầu luyện tập.
     */
    public function formData(): array
    {
        return [
            'types'       => Question::TYPES,
            'difficulties' => Question::DIFFICULTIES,
            'subjects'    => $this->questionBank->subjects(),
            'subjectTopics' => $this->questionBank->topicsBySubject(),
        ];
    }
}
