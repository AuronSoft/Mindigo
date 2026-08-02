<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherCourse\Models\Course;

class CoursePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');
        if (! $course instanceof Course) {
            return false;
        }

        return match ($this->input('publication_status')) {
            Course::PUBLICATION_DRAFT => $this->user()?->can('withdrawReview', $course) ?? false,
            Course::PUBLICATION_PENDING_REVIEW => $this->user()?->can('submitForReview', $course) ?? false,
            Course::PUBLICATION_PUBLISHED => $this->user()?->can('publish', $course) ?? false,
            Course::PUBLICATION_UNLISTED => $course->publication_status === Course::PUBLICATION_PUBLISHED
                && ($this->user()?->can('update', $course) ?? false),
            Course::PUBLICATION_ARCHIVED => $this->user()?->can('archive', $course) ?? false,
            default => false,
        };
    }

    public function rules(): array
    {
        return ['publication_status' => ['required', Rule::in(Course::PUBLICATION_STATUSES)]];
    }
}
