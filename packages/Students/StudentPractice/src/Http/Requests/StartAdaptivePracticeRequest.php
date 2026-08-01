<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartAdaptivePracticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('skill')) ?? false;
    }

    public function rules(): array
    {
        return ['question_count' => ['required', 'integer', 'min:3', 'max:30']];
    }
}
