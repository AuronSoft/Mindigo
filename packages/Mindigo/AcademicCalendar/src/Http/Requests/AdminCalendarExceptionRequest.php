<?php

namespace Mindigo\AcademicCalendar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminCalendarExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'course_id' => $this->input('scope') === 'course' ? $this->input('course_id') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'scope' => ['required', Rule::in(['global', 'course'])],
            'course_id' => ['nullable', 'required_if:scope,course', 'integer', 'exists:courses,id'],
            'exception_date' => ['required', 'date_format:Y-m-d'],
            'title' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
