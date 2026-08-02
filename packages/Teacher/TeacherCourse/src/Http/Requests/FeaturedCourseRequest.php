<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeaturedCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'is_featured' => ['required', 'boolean'],
            'featured_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ];
    }
}
