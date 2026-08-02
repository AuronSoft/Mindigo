<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'scope' => ['nullable', Rule::in(['course', 'teacher', 'student', 'classroom'])],
            'entity_id' => ['nullable', 'integer', 'min:1'],
            'format' => ['nullable', Rule::in(['csv', 'xlsx', 'pdf'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
