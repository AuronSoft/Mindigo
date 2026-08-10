<?php

namespace Mindigo\TeacherCalendar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

class CompleteCalendarSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $schedule = $this->route('schedule');

        return $this->user()?->role === 'teacher'
            && $schedule?->classroom?->teacher_id === (int) $this->user()->getAuthIdentifier()
            && $schedule?->status === ClassroomSchedule::STATUS_SCHEDULED;
    }

    public function rules(): array
    {
        return [];
    }
}
