<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherCourse\Models\CourseReview;

class CourseReviewModerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $review = $this->route('review');

        return $review instanceof CourseReview && ($this->user()?->can('moderate', $review) ?? false);
    }

    public function rules(): array
    {
        return [
            'moderation_status' => ['required', Rule::in(CourseReview::MODERATION_STATUSES)],
            'moderation_reason' => ['nullable', 'required_if:moderation_status,hidden', 'string', 'max:1000'],
        ];
    }
}
