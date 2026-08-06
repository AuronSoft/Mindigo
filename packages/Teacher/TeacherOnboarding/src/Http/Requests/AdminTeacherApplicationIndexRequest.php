<?php

namespace Mindigo\TeacherOnboarding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;

class AdminTeacherApplicationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', TeacherApplication::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in([
                TeacherApplication::STATUS_SUBMITTED,
                TeacherApplication::STATUS_SCREENING,
                TeacherApplication::STATUS_NEED_MORE_INFO,
                TeacherApplication::STATUS_REJECTED,
            ])],
            'application_type' => ['nullable', Rule::in(TeacherApplication::APPLICATION_TYPES)],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'name'])],
        ];
    }
}
