<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'in:active,inactive'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
