<?php

namespace Mindigo\TeacherLiveSession\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LiveSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'classroom_id' => 'required|integer|exists:classrooms,id',
            'scheduled_start' => 'required|date',
            'scheduled_end' => 'nullable|date|after:scheduled_start',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('teacher-live-session::app.validation.title_required'),
            'classroom_id.required' => __('teacher-live-session::app.validation.classroom_required'),
            'classroom_id.exists' => __('teacher-live-session::app.validation.classroom_exists'),
            'scheduled_start.required' => __('teacher-live-session::app.validation.start_required'),
            'scheduled_end.after' => __('teacher-live-session::app.validation.end_after'),
        ];
    }
}
