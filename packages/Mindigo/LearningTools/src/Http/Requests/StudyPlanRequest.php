<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudyPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('learning-tools.use') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['active', 'completed', 'archived'])],
        ];
    }
}
