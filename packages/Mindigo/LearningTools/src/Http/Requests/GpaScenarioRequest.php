<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GpaScenarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('learning-tools.use') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'courses' => ['required', 'array', 'min:1', 'max:20'],
            'courses.*.name' => ['nullable', 'string', 'max:180'],
            'courses.*.credits' => ['nullable', 'integer', 'min:1', 'max:20'],
            'courses.*.score' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'courses.*.components' => ['nullable', 'array', 'max:6'],
            'courses.*.components.*.name' => ['nullable', 'string', 'max:120'],
            'courses.*.components.*.score' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'courses.*.components.*.weight' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
        ];
    }
}
