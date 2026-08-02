<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherCourse\Models\Course;

class CourseCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'category_id' => ['nullable', 'integer', 'exists:course_categories,id'],
            'education_level' => ['nullable', Rule::in(Course::EDUCATION_LEVELS)],
            'difficulty' => ['nullable', Rule::in(Course::DIFFICULTIES)],
            'sort' => ['nullable', Rule::in(['newest', 'popular', 'rating', 'enrolled'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
