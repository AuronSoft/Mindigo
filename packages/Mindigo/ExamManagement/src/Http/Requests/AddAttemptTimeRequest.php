<?php

namespace Mindigo\ExamManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddAttemptTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['minutes' => ['required', 'integer', 'min:1', 'max:180']];
    }
}
