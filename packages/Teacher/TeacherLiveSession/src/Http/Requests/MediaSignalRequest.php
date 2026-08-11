<?php

namespace Mindigo\TeacherLiveSession\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MediaSignalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:4096'],
            'recipient_id' => ['nullable', 'integer'],
            'recipient_key' => ['required_without:recipient_id', 'string', 'regex:/^(user|guest):[1-9][0-9]*$/'],
            'type' => ['required', Rule::in(['offer', 'answer', 'ice'])],
            'payload' => [
                'required',
                'array',
                static function (string $attribute, mixed $value, \Closure $fail): void {
                    if (strlen(json_encode($value, JSON_THROW_ON_ERROR)) > 65_535) {
                        $fail(__('teacher-live-session::app.validation.signal_too_large'));
                    }
                },
            ],
        ];
    }
}
