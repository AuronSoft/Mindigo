<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FlashcardReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('learning-tools.use') ?? false;
    }

    public function rules(): array
    {
        return ['rating' => ['required', Rule::in(['again', 'hard', 'good', 'easy'])]];
    }
}
