<?php

namespace Mindigo\TeacherLiveSession\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateGuestLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['ttl_minutes' => ['required', 'integer', 'min:15', 'max:10080'], 'max_uses' => ['nullable', 'integer', 'min:1', 'max:1000']];
    }
}
