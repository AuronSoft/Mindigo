<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherCourse\Models\Course;

class AdminCourseReviewActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course instanceof Course && ($this->user()?->can('review', $course) ?? false);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['approve', 'request_changes'])],
            'review_note' => [
                Rule::requiredIf($this->input('action') === 'request_changes'),
                'nullable',
                'string',
                'min:10',
                'max:2000',
            ],
        ];
    }
}
