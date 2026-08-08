<?php

namespace Mindigo\TeacherDiscussion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePinnedMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'is_pinned' => ['required', 'boolean'],
        ];
    }
}
