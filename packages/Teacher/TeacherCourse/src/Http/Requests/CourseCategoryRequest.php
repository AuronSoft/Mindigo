<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherCourse\Models\CourseCategory;

class CourseCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function authorize(): bool
    {
        $category = $this->route('course_category');

        return $category instanceof CourseCategory
            ? ($this->user()?->can('update', $category) ?? false)
            : ($this->user()?->can('create', CourseCategory::class) ?? false);
    }

    public function rules(): array
    {
        $category = $this->route('course_category');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('course_categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }
}
