<?php

namespace Mindigo\SubjectManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\SubjectManagement\Models\Subject;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('subject') ? 'subjects.update' : 'subjects.create';

        return $this->user()?->hasPermissionTo($permission) ?? false;
    }

    public function rules(): array
    {
        $subject = $this->route('subject');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('subjects', 'name')->ignore($subject?->id)],
            'code' => ['required', 'string', 'max:40', Rule::unique('subjects', 'code')->ignore($subject?->id)],
            'description' => ['nullable', 'string', 'max:3000'],
            'color' => ['required', Rule::in(Subject::COLORS)],
            'icon' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(Subject::STATUSES)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }
}
