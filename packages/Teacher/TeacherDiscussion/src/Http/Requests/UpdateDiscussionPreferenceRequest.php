<?php

namespace Mindigo\TeacherDiscussion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscussionPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'is_muted' => ['sometimes', 'required', 'boolean'],
            'is_pinned' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
