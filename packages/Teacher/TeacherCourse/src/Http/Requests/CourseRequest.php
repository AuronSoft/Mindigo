<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherCourse\Models\Course;

class CourseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('starts_at') && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', (string) $this->input('starts_at'))) {
            $this->merge(['starts_at' => Carbon::createFromFormat('d/m/Y', (string) $this->input('starts_at'))?->format('Y-m-d')]);
        }

        if ($this->filled('study_time_start') && $this->filled('study_time_end')) {
            $this->merge(['study_time' => $this->input('study_time_start').' - '.$this->input('study_time_end')]);
        }

        if ($this->input('access_type') === 'free') {
            $this->merge(['price' => 0]);
        }

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
            'access_type' => ['sometimes', Rule::in(Course::ACCESS_TYPES)],
            'price' => ['nullable', 'required_if:access_type,paid', 'numeric', 'min:0', 'max:999999999'],
            'currency' => ['sometimes', 'string', Rule::in(['VND'])],
            'starts_at' => ['nullable', 'date'],
            'schedule_days' => ['nullable', 'array'],
            'schedule_days.*' => ['string', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'study_time_start' => ['nullable', 'date_format:H:i'],
            'study_time_end' => ['nullable', 'date_format:H:i'],
            'study_time' => ['nullable', 'string', 'max:120'],
            'learning_outcomes' => ['nullable', 'string', 'max:10000'],
            'requirements' => ['nullable', 'string', 'max:10000'],
            'target_learners' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
