<?php

namespace Mindigo\SubjectManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\SubjectManagement\Models\SubjectTopic;

class SubjectTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('topic') ? 'subjects.update' : 'subjects.create';

        return $this->user()?->hasPermissionTo($permission) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(SubjectTopic::STATUSES)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }
}
