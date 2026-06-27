<?php

namespace Mindigo\TeacherAssignment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignmentRequest extends FormRequest
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
            'due_date' => 'required|date|after:now',
            'allow_late' => 'boolean',
            'late_days' => 'nullable|integer|min:1|max:30|required_if:allow_late,true',
            'max_score' => 'required|integer|min:1|max:10',
            'files' => 'nullable|array|max:10',
            'remove_files' => 'nullable|array',
            'remove_files.*' => 'string',
            'files.*' => 'file|mimes:pdf,docx,doc,zip,rar,xlsx,xls,pptx,ppt,jpg,jpeg,png|max:20480',
            'submission_type' => 'required|in:file,text,both',
            'status' => 'required|in:draft,published',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('teacher-assignment::app.validation.title_required'),
            'classroom_id.required' => __('teacher-assignment::app.validation.classroom_required'),
            'classroom_id.exists' => __('teacher-assignment::app.validation.classroom_exists'),
            'due_date.required' => __('teacher-assignment::app.validation.due_date_required'),
            'due_date.after' => __('teacher-assignment::app.validation.due_date_after'),
            'max_score.required' => __('teacher-assignment::app.validation.max_score_required'),
            'late_days.required_if' => __('teacher-assignment::app.validation.late_days_required'),
            'files.max' => __('teacher-assignment::app.validation.files_max_count'),
            'files.*.mimes' => __('teacher-assignment::app.validation.file_mimes'),
            'files.*.max' => __('teacher-assignment::app.validation.file_max_size'),
        ];
    }
}
