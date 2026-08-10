<?php

namespace Mindigo\TeacherCalendar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

class OpenCalendarAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ClassroomSchedule|null $schedule */
        $schedule = $this->route('schedule');

        return $schedule?->classroom?->teacher_id === (int) $this->user()?->getAuthIdentifier();
    }

    public function rules(): array
    {
        return ['duration_minutes' => ['required', 'integer', 'in:15,30,45,60,90,120']];
    }
}
