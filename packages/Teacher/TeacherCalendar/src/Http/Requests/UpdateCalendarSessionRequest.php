<?php

namespace Mindigo\TeacherCalendar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

class UpdateCalendarSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $schedule = $this->route('schedule');

        return $this->user()?->role === 'teacher'
            && $schedule?->classroom?->teacher_id === (int) $this->user()->getAuthIdentifier()
            && ! in_array($schedule?->status, [ClassroomSchedule::STATUS_CANCELLED, ClassroomSchedule::STATUS_RESCHEDULED, ClassroomSchedule::STATUS_COMPLETED], true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'delivery_mode' => ['required', Rule::in(ClassroomSchedule::DELIVERY_MODES)],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'url:http,https', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
