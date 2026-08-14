<?php

namespace Mindigo\ExamManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendAttemptWarningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['message' => ['required', 'string', 'max:1000']];
    }
}
