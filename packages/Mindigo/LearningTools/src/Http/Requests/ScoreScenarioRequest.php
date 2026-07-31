<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScoreScenarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('learning-tools.use') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'combination_code' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9_-]+$/'],
            'scores' => ['required', 'array', 'size:3'],
            'scores.*' => ['required', 'numeric', 'min:0', 'max:10'],
            'priority_score' => ['nullable', 'numeric', 'min:0', 'max:3'],
            'bonus_score' => ['nullable', 'numeric', 'min:0', 'max:3'],
        ];
    }
}
