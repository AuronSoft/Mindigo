<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FlashcardDeckRequest extends FormRequest
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
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'visibility' => ['required', Rule::in(['private', 'public'])],
            'classroom_ids' => ['nullable', 'array'],
            'classroom_ids.*' => ['integer', 'distinct', 'exists:classrooms,id'],
        ];
    }
}
