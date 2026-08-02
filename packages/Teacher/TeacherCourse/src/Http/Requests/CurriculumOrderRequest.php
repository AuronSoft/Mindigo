<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\TeacherCourse\Models\Course;

class CurriculumOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course instanceof Course && ($this->user()?->can('update', $course) ?? false);
    }

    public function rules(): array
    {
        return [
            'chapters' => ['required', 'array'],
            'chapters.*.id' => ['required', 'integer', 'distinct'],
            'chapters.*.order' => ['required', 'integer', 'min:0'],
            'chapters.*.lessons' => ['present', 'array'],
            'chapters.*.lessons.*.id' => ['required', 'integer'],
            'chapters.*.lessons.*.order' => ['required', 'integer', 'min:0'],
        ];
    }
}
