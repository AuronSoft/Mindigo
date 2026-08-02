<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherCourse\Models\Course;

class CourseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('duration_value') && $this->filled('duration_unit')) {
            $minutes = config('course.duration_minutes.'.$this->input('duration_unit'));
            if (is_numeric($minutes)) {
                $this->merge(['estimated_duration_minutes' => (int) round((float) $this->input('duration_value') * (float) $minutes)]);
            }
        }
    }

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
            'duration_value' => ['nullable', 'required_with:duration_unit', 'numeric', 'gt:0', 'max:525600'],
            'duration_unit' => ['nullable', 'required_with:duration_value', Rule::in(Course::DURATION_UNITS)],
            'learning_outcomes' => ['nullable', 'string', 'max:10000'],
            'requirements' => ['nullable', 'string', 'max:10000'],
            'target_learners' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
