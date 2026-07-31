<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlashcardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('learning-tools.use') ?? false;
    }

    public function rules(): array
    {
        return [
            'front' => ['required', 'string', 'max:5000'],
            'back' => ['required', 'string', 'max:10000'],
        ];
    }
}
