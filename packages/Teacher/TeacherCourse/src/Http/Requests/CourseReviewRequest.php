<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseReview;

class CourseReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $review = $this->route('review');
        if ($review instanceof CourseReview) {
            return $this->user()?->can('update', $review) ?? false;
        }

        return $this->user()?->isStudent() && $this->route('course') instanceof Course;
    }

    public function rules(): array
    {
        return ['rating' => ['required', 'integer', 'between:1,5'], 'comment' => ['nullable', 'string', 'min:3', 'max:2000']];
    }
}
