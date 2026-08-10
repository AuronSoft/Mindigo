<?php

namespace Mindigo\TeacherClassroom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\TeacherClassroom\Models\Classroom;

class GenerateCourseScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Classroom|null $classroom */
        $classroom = $this->route('classroom');

        return $classroom?->teacher_id === (int) $this->user()?->getAuthIdentifier();
    }

    public function rules(): array
    {
        /** @var Classroom|null $classroom */
        $classroom = $this->route('classroom');
        $courseStart = $classroom?->course?->starts_at?->toDateString();

        return [
            'start_date' => ['required', 'date_format:Y-m-d', ...($courseStart ? ['after_or_equal:'.$courseStart] : [])],
            'session_count' => ['required', 'integer', 'min:1', 'max:200'],
        ];
    }
}
