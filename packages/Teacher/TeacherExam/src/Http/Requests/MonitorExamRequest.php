<?php

namespace Mindigo\TeacherExam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MonitorExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'classroom' => ['nullable', 'integer', 'exists:classrooms,id'],
            'status' => ['nullable', Rule::in(['not_started', 'in_progress', 'submitted', 'expired'])],
            'sort' => ['nullable', Rule::in(['name', 'status', 'progress', 'remaining', 'last_activity'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
