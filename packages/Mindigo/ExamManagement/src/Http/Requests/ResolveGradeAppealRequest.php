<?php

namespace Mindigo\ExamManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveGradeAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() === true;
    }

    public function rules(): array
    {
        return ['status' => ['required', 'in:upheld,rejected'], 'resolution' => ['required', 'string', 'max:3000']];
    }
}
