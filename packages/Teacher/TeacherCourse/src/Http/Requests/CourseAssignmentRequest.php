<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseClassroomAssignment;

class CourseAssignmentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_mandatory' => $this->has('is_mandatory') ? $this->boolean('is_mandatory') : true,
            'visibility' => $this->input('visibility', 'visible'),
        ]);
    }

    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course instanceof Course && ($this->user()?->can('view', $course) ?? false);
    }

    public function rules(): array
    {
        return [
            'classroom_ids' => ['required', 'array', 'min:1'],
            'classroom_ids.*' => ['required', 'integer', 'distinct', 'exists:classrooms,id'],
            'starts_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date', 'after:starts_at'],
            'is_mandatory' => ['required', 'boolean'],
            'visibility' => ['required', Rule::in(CourseClassroomAssignment::VISIBILITIES)],
        ];
    }
}
