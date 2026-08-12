<?php

namespace Mindigo\TeacherLiveSession\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Enums\LiveSessionType;
use Mindigo\TeacherLiveSession\Models\LiveProviderConnection;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;
use Mindigo\TeacherLiveSession\Services\LiveProviderOAuthService;

class LiveSessionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $session = $this->route('liveSession');
        $classroom = $this->ownedClassroom();
        $existingSettings = $session instanceof LiveSession ? ($session->room_settings ?? []) : [];

        $this->merge([
            'provider' => $this->input('provider', $session instanceof LiveSession
                ? $session->provider->value
                : LiveSessionProvider::Native->value),
            'session_type' => $classroom?->type === Classroom::TYPE_COURSE
                ? $this->input('session_type', LiveSessionType::Regular->value)
                : LiveSessionType::Flexible->value,
            'room_settings' => [
                'waiting_room_enabled' => $this->booleanSetting('waiting_room_enabled', $existingSettings, true),
                'guest_access_enabled' => $this->booleanSetting('guest_access_enabled', $existingSettings, false),
                'chat_enabled' => $this->booleanSetting('chat_enabled', $existingSettings, true),
                'private_chat_enabled' => $this->booleanSetting('private_chat_enabled', $existingSettings, false),
                'student_microphone_enabled' => $this->booleanSetting('student_microphone_enabled', $existingSettings, true),
                'student_camera_enabled' => $this->booleanSetting('student_camera_enabled', $existingSettings, true),
                'student_screen_share_enabled' => $this->booleanSetting('student_screen_share_enabled', $existingSettings, false),
                'recording_enabled' => $this->booleanSetting('recording_enabled', $existingSettings, false),
            ],
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
            'description' => ['nullable', 'string', 'max:2000'],
            'classroom_id' => [
                'required',
                'integer',
                Rule::exists('classrooms', 'id')->where(fn ($query) => $query
                    ->where('teacher_id', $teacherId)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')),
            ],
            'classroom_schedule_id' => ['nullable', 'integer', 'exists:classroom_schedules,id'],
            'session_type' => ['required', Rule::enum(LiveSessionType::class)],
            'provider' => ['required', Rule::in(array_keys($providers->capabilities()))],
            'scheduled_start' => ['required', 'date'],
            'scheduled_end' => ['required', 'date', 'after:scheduled_start'],
            'room_settings' => ['required', 'array'],
            'room_settings.waiting_room_enabled' => ['required', 'boolean'],
            'room_settings.guest_access_enabled' => ['required', 'boolean'],
            'room_settings.chat_enabled' => ['required', 'boolean'],
            'room_settings.private_chat_enabled' => ['required', 'boolean'],
            'room_settings.student_microphone_enabled' => ['required', 'boolean'],
            'room_settings.student_camera_enabled' => ['required', 'boolean'],
            'room_settings.student_screen_share_enabled' => ['required', 'boolean'],
            'room_settings.recording_enabled' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $classroom = $this->ownedClassroom();
            if (! $classroom) {
                return;
            }

            $session = $this->route('liveSession');
            $schedule = $this->filled('classroom_schedule_id')
                ? ClassroomSchedule::query()->find($this->integer('classroom_schedule_id'))
                : null;

            $this->validateAcademicContext($validator, $classroom, $schedule, $session);
            $this->validateProviderSettings($validator, app(LiveMeetingProviderRegistry::class));
            $this->validateConflicts($validator, $classroom, $session);
        }];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('teacher-live-session::app.validation.title_required'),
            'classroom_id.required' => __('teacher-live-session::app.validation.classroom_required'),
            'classroom_id.exists' => __('teacher-live-session::app.validation.classroom_exists'),
            'scheduled_start.required' => __('teacher-live-session::app.validation.start_required'),
            'scheduled_end.required' => __('teacher-live-session::app.validation.end_required'),
            'scheduled_end.after' => __('teacher-live-session::app.validation.end_after'),
            'provider.in' => __('teacher-live-session::app.validation.provider_unavailable'),
        ];
    }

    private function validateAcademicContext(Validator $validator, Classroom $classroom, ?ClassroomSchedule $schedule, mixed $current): void
    {
        $isCourseClass = $classroom->type === Classroom::TYPE_COURSE && $classroom->course_id;

        if ($isCourseClass && ! $schedule) {
            $validator->errors()->add('classroom_schedule_id', __('teacher-live-session::app.validation.schedule_required_for_course'));

            return;
        }

        if (! $isCourseClass && $this->input('session_type') !== LiveSessionType::Flexible->value) {
            $validator->errors()->add('session_type', __('teacher-live-session::app.validation.standalone_flexible_only'));
        }

        if (! $schedule) {
            return;
        }

        if ((int) $schedule->classroom_id !== (int) $classroom->id) {
            $validator->errors()->add('classroom_schedule_id', __('teacher-live-session::app.validation.schedule_classroom'));

            return;
        }

        if (! in_array($schedule->status, [ClassroomSchedule::STATUS_DRAFT, ClassroomSchedule::STATUS_SCHEDULED], true)) {
            $validator->errors()->add('classroom_schedule_id', __('teacher-live-session::app.validation.schedule_unavailable'));
        }

        $alreadyLinked = LiveSession::query()
            ->where('classroom_schedule_id', $schedule->id)
            ->when($current instanceof LiveSession, fn ($query) => $query->whereKeyNot($current->id))
            ->exists();
        if ($alreadyLinked) {
            $validator->errors()->add('classroom_schedule_id', __('teacher-live-session::app.validation.schedule_already_linked'));
        }

        if ($isCourseClass && $this->input('session_type') !== $schedule->type) {
            $validator->errors()->add('session_type', __('teacher-live-session::app.validation.session_type_mismatch'));
        }

        if ($isCourseClass) {
            $expectedStart = Carbon::parse($schedule->session_date->format('Y-m-d').' '.$schedule->start_time);
            $expectedEnd = Carbon::parse($schedule->session_date->format('Y-m-d').' '.$schedule->end_time);
            if (! Carbon::parse($this->input('scheduled_start'))->equalTo($expectedStart)) {
                $validator->errors()->add('scheduled_start', __('teacher-live-session::app.validation.schedule_start_mismatch'));
            }
            if (! Carbon::parse($this->input('scheduled_end'))->equalTo($expectedEnd)) {
                $validator->errors()->add('scheduled_end', __('teacher-live-session::app.validation.schedule_end_mismatch'));
            }
        }
    }

    private function validateProviderSettings(Validator $validator, LiveMeetingProviderRegistry $providers): void
    {
        $provider = $providers->resolve($this->string('provider')->toString());
        $providerKey = $provider->key();
        $settings = $this->input('room_settings', []);

        if ($providerKey->isExternal()) {
            $connected = LiveProviderConnection::query()
                ->where('user_id', $this->user()->getAuthIdentifier())
                ->where('provider', $providerKey->value)
                ->whereNull('revoked_at')->exists();
            if (! app(LiveProviderOAuthService::class)->isConfigured($providerKey) || ! $connected) {
                $validator->errors()->add('provider', __('teacher-live-session::app.validation.provider_not_connected'));
            }
        }

        if (($settings['recording_enabled'] ?? false) && ! $provider->capabilities()->recording) {
            $validator->errors()->add('room_settings.recording_enabled', __('teacher-live-session::app.validation.recording_unavailable'));
        }

        if (($settings['guest_access_enabled'] ?? false) && ! $provider->capabilities()->guestLinks) {
            $validator->errors()->add('room_settings.guest_access_enabled', __('teacher-live-session::app.validation.guest_access_unavailable'));
        }
    }

    private function validateConflicts(Validator $validator, Classroom $classroom, mixed $current): void
    {
        $start = Carbon::parse($this->input('scheduled_start'));
        $end = Carbon::parse($this->input('scheduled_end'));
        $activeStatuses = ['scheduled', 'live'];

        if (! $current instanceof LiveSession && $end->isPast()) {
            $validator->errors()->add('scheduled_end', __('teacher-live-session::app.validation.session_in_past'));

            return;
        }

        $base = LiveSession::query()
            ->whereIn('status', $activeStatuses)
            ->where('scheduled_start', '<', $end)
            ->where('scheduled_end', '>', $start)
            ->when($current instanceof LiveSession, fn ($query) => $query->whereKeyNot($current->id));

        if ((clone $base)->where('classroom_id', $classroom->id)->exists()) {
            $validator->errors()->add('scheduled_start', __('teacher-live-session::app.validation.classroom_conflict'));
        }

        if ((clone $base)->where('teacher_id', $classroom->teacher_id)->exists()) {
            $validator->errors()->add('scheduled_start', __('teacher-live-session::app.validation.teacher_conflict'));
        }
    }

    private function ownedClassroom(): ?Classroom
    {
        if (! $this->filled('classroom_id') || ! $this->user()) {
            return null;
        }

        return Classroom::query()
            ->whereKey($this->integer('classroom_id'))
            ->where('teacher_id', $this->user()->getAuthIdentifier())
            ->where('status', 'active')
            ->first();
    }

    private function booleanSetting(string $key, array $existing, bool $default): bool
    {
        if ($this->has("room_settings.{$key}")) {
            return $this->boolean("room_settings.{$key}");
        }

        return (bool) ($existing[$key] ?? $default);
    }
}
