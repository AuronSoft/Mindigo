<?php

namespace Mindigo\TeacherOnboarding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Mindigo\TeacherOnboarding\Models\TeacherApplicationInterview;

class TeacherApplicationInterviewRequest extends FormRequest
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
            'scheduled_at' => ['required', 'date', 'after:now'],
            'mode' => ['required', Rule::in(TeacherApplicationInterview::MODES)],
            'meeting_url' => [
                Rule::requiredIf(fn (): bool => $this->input('mode') === TeacherApplicationInterview::MODE_ONLINE),
                'nullable',
                'url',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value) {
                        return;
                    }

                    $host = parse_url((string) $value, PHP_URL_HOST) ?: '';
                    if (! str_contains($host, 'meet.google.com') && ! str_contains($host, 'zoom.us') && ! str_contains($host, 'jitsi')) {
                        $fail(__('teacher-onboarding::interview.invalid_meeting_url'));
                    }
                },
            ],
            'pre_interview_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
