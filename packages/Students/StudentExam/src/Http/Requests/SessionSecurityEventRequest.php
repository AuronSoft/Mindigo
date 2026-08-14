<?php

namespace Mindigo\StudentExam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;

class SessionSecurityEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attempt = $this->route('attempt');

        return $attempt instanceof ExamSessionAttempt && (int) $attempt->user_id === (int) $this->user()?->getAuthIdentifier();
    }

    public function rules(): array
    {
        return ['type' => ['required', 'in:tab_hidden,fullscreen_exit,copy,paste'], 'occurred_at' => ['nullable', 'date']];
    }
}
