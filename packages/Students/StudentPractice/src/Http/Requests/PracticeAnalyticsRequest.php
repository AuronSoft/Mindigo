<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PracticeAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['student', 'admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'period' => ['nullable', 'string', Rule::in(['7', '30', '90', 'all'])],
            'skill_id' => [
                'nullable', 'integer',
                Rule::exists('practice_skills', 'id')->where('status', 'active')->whereNull('deleted_at'),
            ],
        ];
    }
}
