<?php

namespace Mindigo\TeacherClassroom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\TeacherClassroom\Models\Classroom;

class StoreCalendarExceptionRequest extends FormRequest
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
        $course = $classroom?->course;

        return [
            'exception_date' => ['required', 'date_format:Y-m-d', ...($course?->starts_at ? ['after_or_equal:'.$course->starts_at->toDateString()] : []), ...($course?->ends_at ? ['before_or_equal:'.$course->ends_at->toDateString()] : [])],
            'title' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
