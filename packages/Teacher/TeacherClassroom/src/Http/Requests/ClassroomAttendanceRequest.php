<?php

namespace Mindigo\TeacherClassroom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassroomAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'attendance_date' => ['required', 'date'],
            'classroom_schedule_id' => ['nullable', 'integer', 'exists:classroom_schedules,id'],
            'records' => ['required', 'array'],
            'records.*.status' => ['required', 'string', 'in:present,absent,late,excused'],
            'records.*.late_minutes' => ['nullable', 'required_if:records.*.status,late', 'integer', 'min:1', 'max:600'],
            'records.*.absence_reason' => ['nullable', 'required_if:records.*.status,excused', 'string', 'min:5', 'max:500'],
            'records.*.remarks' => ['nullable', 'string', 'max:255'],
            'change_reason' => ['nullable', 'string', 'min:5', 'max:500'],
        ];
    }
}
