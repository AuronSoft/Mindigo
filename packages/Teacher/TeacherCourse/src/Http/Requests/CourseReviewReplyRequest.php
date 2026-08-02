<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\TeacherCourse\Models\CourseReview;

class CourseReviewReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $review = $this->route('review');

        return $review instanceof CourseReview && ($this->user()?->can('reply', $review) ?? false);
    }

    public function rules(): array
    {
        return ['teacher_reply' => ['required', 'string', 'min:2', 'max:2000']];
    }
}
