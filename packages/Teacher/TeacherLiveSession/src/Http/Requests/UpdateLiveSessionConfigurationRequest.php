<?php

namespace Mindigo\TeacherLiveSession\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateLiveSessionConfigurationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['live_google_meet_enabled', 'live_zoom_enabled', 'live_recording_enabled', 'live_recording_consent_required'] as $key) {
            $this->merge([$key => $this->boolean($key)]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'live_google_meet_enabled' => ['required', 'boolean'], 'live_zoom_enabled' => ['required', 'boolean'],
            'live_max_participants' => ['required', 'integer', 'between:2,1000'],
            'live_max_duration_minutes' => ['required', 'integer', 'between:15,1440'],
            'live_max_sessions_per_teacher_daily' => ['required', 'integer', 'between:1,200'],
            'live_max_bitrate_kbps' => ['required', 'integer', 'between:128,10000'],
            'live_recording_enabled' => ['required', 'boolean'],
            'live_recording_max_minutes' => ['required', 'integer', 'between:15,1440'],
            'live_data_retention_days' => ['required', 'integer', 'between:30,3650'],
            'live_recording_retention_days' => ['required', 'integer', 'between:1,3650'],
            'live_recording_consent_required' => ['required', 'boolean'],
        ];
    }
}
