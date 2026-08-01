<?php

namespace Mindigo\StudentPractice\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAnswer;
use Mindigo\StudentPractice\Models\PracticeAttempt;

interface PracticeServiceInterface
{
    /**
     * Lấy danh sách câu hỏi approved để luyện tập theo filter.
     */
    public function getQuestions(array $filters): LengthAwarePaginator;

    /**
     * Lấy một câu hỏi theo ID (chỉ approved).
     */
    public function getQuestion(int $id): ?Question;

    /**
     * Dữ liệu cho form filter / bắt đầu luyện tập.
     */
    public function formData(User $user): array;

    /**
     * Bắt đầu một bài luyện tập mới.
     */
    public function startPractice(User $student, array $data): PracticeAttempt;

    /**
     * Lưu câu trả lời của học sinh.
     */
    public function submitAnswer(PracticeAttempt $attempt, int $questionId, array $answer): PracticeAnswer;

    /**
     * Hoàn thành bài luyện tập và tính điểm.
     */
    public function completePractice(PracticeAttempt $attempt): PracticeAttempt;

    /**
     * Lấy lịch sử luyện tập của học sinh.
     */
    public function getStudentHistory(User $student, int $limit = 10): Collection;

    /**
     * Lấy thống kê luyện tập của học sinh.
     */
    public function getStudentStats(User $student): array;

    /**
     * Lấy chi tiết một bài luyện tập với các câu trả lời.
     */
    public function getPracticeDetails(PracticeAttempt $attempt): array;
}
