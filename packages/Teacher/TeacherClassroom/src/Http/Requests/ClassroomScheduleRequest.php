<?php

namespace Mindigo\TeacherClassroom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassroomScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'session_date' => ['required', 'date'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
