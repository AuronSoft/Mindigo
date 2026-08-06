<?php

namespace Mindigo\TeacherOnboarding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;

class AdminTeacherApplicationActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('teacher_application');

        return $application instanceof TeacherApplication
            && ($this->user()?->can('update', $application) ?? false);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                TeacherApplication::STATUS_SCREENING,
                TeacherApplication::STATUS_NEED_MORE_INFO,
                TeacherApplication::STATUS_REJECTED,
            ])],
            'internal_note' => ['nullable', 'string', 'max:2000'],
            'status_note' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('status'), [
                    TeacherApplication::STATUS_NEED_MORE_INFO,
                    TeacherApplication::STATUS_REJECTED,
                ], true)),
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
