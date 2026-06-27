<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
