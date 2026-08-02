<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherCourse\Models\Course;

class CourseIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Course::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        foreach (['search', 'status', 'publication_status'] as $field) {
            if (in_array($this->input($field), ['', '-', 'all'], true)) {
                $this->merge([$field => null]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'publication_status' => ['nullable', Rule::in(Course::PUBLICATION_STATUSES)],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
