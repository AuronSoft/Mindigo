<?php

namespace Mindigo\TeacherOnboarding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;

class TeacherApplicationProvisioningRequest extends FormRequest
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
            'action' => ['required', Rule::in(['approve', 'suspend', 'revoke'])],
            'note' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('action'), ['suspend', 'revoke'], true)),
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
