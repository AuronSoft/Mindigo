<?php

namespace Mindigo\StudentExam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\ExamManagement\Models\ExamAttempt;

class SubmitExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ExamAttempt|null $attempt */
        $attempt = $this->route('attempt');

        return $attempt !== null
            && (int) $attempt->user_id === (int) $this->user()?->getAuthIdentifier();
    }

    public function rules(): array
    {
        return [
            'answers' => ['nullable', 'array', 'max:500'],
            'answers.*' => ['nullable'],
            'answers.*.*' => ['nullable', 'string', 'max:20000'],
            'tab_leave_count' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var ExamAttempt $attempt */
            $attempt = $this->route('attempt');
            $answers = $this->input('answers', []);

            $questionIds = $attempt->exam?->questions()->pluck('id')->map(fn ($id) => (string) $id) ?? collect();
            foreach ($answers as $questionId => $answer) {
                if (! $questionIds->contains((string) $questionId)) {
                    $validator->errors()->add("answers.{$questionId}", __('student-exam::app.invalid_question'));
                }
                if (! is_null($answer) && ! is_scalar($answer) && ! is_array($answer)) {
                    $validator->errors()->add("answers.{$questionId}", __('student-exam::app.invalid_answer'));
                }
                if (is_array($answer) && count($answer) > 100) {
                    $validator->errors()->add("answers.{$questionId}", __('student-exam::app.too_many_answers'));
                }
            }
        });
    }
}
