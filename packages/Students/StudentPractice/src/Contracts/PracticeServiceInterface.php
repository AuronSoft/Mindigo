<?php

namespace Mindigo\StudentPractice\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAnswer;
use Mindigo\StudentPractice\Models\PracticeAttempt;

interface PracticeServiceInterface
{
    public function getQuestions(array $filters): LengthAwarePaginator;

    public function getQuestion(int $id): ?Question;

    public function formData(User $user): array;

    public function startPractice(User $student, array $data): PracticeAttempt;

    public function submitAnswer(PracticeAttempt $attempt, int $questionId, array $answer): PracticeAnswer;

    public function completePractice(PracticeAttempt $attempt): PracticeAttempt;

    public function getStudentHistory(User $student): LengthAwarePaginator;

    public function getStudentStats(User $student): array;

    public function getPracticeDetails(PracticeAttempt $attempt): array;
}
