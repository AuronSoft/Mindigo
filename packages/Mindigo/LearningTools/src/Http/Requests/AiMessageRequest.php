<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('learning-tools.use') ?? false;
    }

    public function rules(): array
    {
        return ['message' => ['required', 'string', 'min:2', 'max:4000']];
    }
}
