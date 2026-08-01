<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompletePracticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('attempt')) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
