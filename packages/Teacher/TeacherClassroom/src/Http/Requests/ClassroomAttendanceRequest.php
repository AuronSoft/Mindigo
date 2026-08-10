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
            'records.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }
}
