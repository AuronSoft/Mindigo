<?php

namespace Mindigo\StudentExam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;

class CameraSnapshotRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attempt = $this->route('attempt');

        return $attempt instanceof ExamSessionAttempt && (int) $attempt->user_id === (int) $this->user()?->getAuthIdentifier();
    }

    public function rules(): array
    {
        return [
            'snapshot' => ['required', 'image', 'mimes:jpg,jpeg,webp', 'max:2048'],
            'session_key' => ['required', 'string', 'max:64'],
            'device_key' => ['required', 'string', 'max:255'],
        ];
    }
}
