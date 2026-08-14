<?php

namespace Mindigo\ExamManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeAttemptAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() === true;
    }

    public function rules(): array
    {
        return [
            'points_awarded' => ['required', 'numeric', 'min:0'],
            'feedback' => ['nullable', 'string', 'max:3000'],
            'rubric_scores' => ['sometimes', 'array'],
            'rubric_scores.*' => ['numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
