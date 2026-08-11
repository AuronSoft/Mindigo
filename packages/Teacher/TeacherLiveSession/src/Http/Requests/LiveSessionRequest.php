<?php

namespace Mindigo\TeacherLiveSession\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;

class LiveSessionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'provider' => $this->input('provider', LiveSessionProvider::Native->value),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(LiveMeetingProviderRegistry $providers): array
    {
        $teacherId = $this->user()?->getAuthIdentifier();

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'classroom_id' => [
                'required',
                'integer',
                Rule::exists('classrooms', 'id')->where(fn ($query) => $query
                    ->where('teacher_id', $teacherId)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')),
            ],
            'classroom_schedule_id' => ['nullable', 'integer', 'exists:classroom_schedules,id'],
            'provider' => ['required', Rule::in(array_keys($providers->capabilities()))],
            'scheduled_start' => ['required', 'date'],
            'scheduled_end' => ['nullable', 'date', 'after:scheduled_start'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->filled('classroom_schedule_id') || ! $this->filled('classroom_id')) {
                return;
            }

            $matchesClassroom = ClassroomSchedule::query()
                ->whereKey($this->integer('classroom_schedule_id'))
                ->where('classroom_id', $this->integer('classroom_id'))
                ->exists();

            if (! $matchesClassroom) {
                $validator->errors()->add('classroom_schedule_id', __('teacher-live-session::app.validation.schedule_classroom'));
            }
        }];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('teacher-live-session::app.validation.title_required'),
            'classroom_id.required' => __('teacher-live-session::app.validation.classroom_required'),
            'classroom_id.exists' => __('teacher-live-session::app.validation.classroom_exists'),
            'scheduled_start.required' => __('teacher-live-session::app.validation.start_required'),
            'scheduled_end.after' => __('teacher-live-session::app.validation.end_after'),
            'provider.in' => __('teacher-live-session::app.validation.provider_unavailable'),
        ];
    }
}
