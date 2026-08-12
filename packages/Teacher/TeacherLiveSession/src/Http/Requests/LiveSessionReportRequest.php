<?php

namespace Mindigo\TeacherLiveSession\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LiveSessionReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['teacher', 'admin'], true);
    }

    public function rules(): array
    {
        return [
            'scope' => ['sometimes', Rule::in(['session', 'classroom', 'course', 'teacher', 'student', 'provider'])],
            'entity_id' => ['nullable', 'integer', 'min:1'],
            'provider' => ['nullable', Rule::in(['native', 'google_meet', 'zoom'])],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'format' => ['sometimes', Rule::in(['csv', 'xlsx', 'pdf'])],
        ];
    }
}
