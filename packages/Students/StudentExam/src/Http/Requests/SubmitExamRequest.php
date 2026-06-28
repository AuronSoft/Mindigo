<?php

namespace Mindigo\StudentExam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // answers[question_id] = option_id (trắc nghiệm 1 đáp án)
            // answers[question_id][] = [id, id] (nhiều đáp án)
            // answers[question_id] = 'text' (tự luận)
            'answers'              => ['nullable', 'array'],
            'answers.*'            => ['nullable'],
            'tab_leave_count'      => ['nullable', 'integer', 'min:0'],
        ];
    }
}