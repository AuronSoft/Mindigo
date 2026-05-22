<?php

namespace Mindigo\ClassroomManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassroomRosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('classrooms.manage_students') ?? false;
    }

    public function rules(): array
    {
        return [
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => [Rule::exists('users', 'id')->where('role', 'student')],
        ];
    }

    public function studentIds(): array
    {
        return collect($this->validated('student_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
