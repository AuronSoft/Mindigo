<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherCourse\Models\Course;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course instanceof Course
            ? ($this->user()?->can('update', $course) ?? false)
            : ($this->user()?->can('create', Course::class) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')->where('status', 'active')->whereNull('deleted_at')],
            'category_id' => ['nullable', 'integer', Rule::exists('course_categories', 'id')->where('is_active', true)],
            'education_level' => ['nullable', Rule::in(Course::EDUCATION_LEVELS)],
            'difficulty' => ['sometimes', Rule::in(Course::DIFFICULTIES)],
            'language' => ['sometimes', 'string', 'max:10'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:525600'],
            'learning_outcomes' => ['nullable', 'string', 'max:10000'],
            'requirements' => ['nullable', 'string', 'max:10000'],
            'target_learners' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
