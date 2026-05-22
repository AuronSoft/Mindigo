<?php

namespace Mindigo\ClassroomManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassroomSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('classrooms.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => [Rule::exists('subjects', 'id')->where('status', 'active')],
        ];
    }

    public function subjectIds(): array
    {
        return collect($this->validated('subject_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
