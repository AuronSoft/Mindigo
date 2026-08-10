<?php

namespace Mindigo\TeacherCalendar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

class RespondSubstituteAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ClassroomSchedule|null $schedule */
        $schedule = $this->route('schedule');

        return $schedule?->substitute_teacher_id === (int) $this->user()?->getAuthIdentifier();
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['accept', 'decline'])],
            'response_note' => [Rule::requiredIf($this->input('decision') === 'decline'), 'nullable', 'string', 'min:10', 'max:500'],
        ];
    }
}
