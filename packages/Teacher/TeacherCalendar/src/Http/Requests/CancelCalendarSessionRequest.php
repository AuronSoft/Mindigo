<?php

namespace Mindigo\TeacherCalendar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelCalendarSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $schedule = $this->route('schedule');

        return $this->user()?->role === 'teacher'
            && $schedule?->classroom?->teacher_id === (int) $this->user()->getAuthIdentifier();
    }

    public function rules(): array
    {
        return ['cancel_reason' => ['required', 'string', 'min:10', 'max:1000']];
    }
}
