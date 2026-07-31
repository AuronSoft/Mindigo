<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudyPlanTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('learning-tools.use') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
