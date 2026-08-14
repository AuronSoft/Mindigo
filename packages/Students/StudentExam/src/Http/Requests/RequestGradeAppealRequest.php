<?php

namespace Mindigo\StudentExam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestGradeAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() === true;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:3000']];
    }
}
