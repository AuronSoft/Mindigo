<?php

namespace Mindigo\StudentNotebook\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'   => 'required|string|max:255',
            'content' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('student-notebook::app.validation.title_required'),
            'title.max'      => __('student-notebook::app.validation.title_max'),
            'content.max'    => __('student-notebook::app.validation.content_max'),
        ];
    }
}
