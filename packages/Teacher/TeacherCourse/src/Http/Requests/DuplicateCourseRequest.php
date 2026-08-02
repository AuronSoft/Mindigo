<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\TeacherCourse\Models\Course;

class DuplicateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course instanceof Course
            && ($this->user()?->can('view', $course) ?? false)
            && ($this->user()?->can('create', Course::class) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
