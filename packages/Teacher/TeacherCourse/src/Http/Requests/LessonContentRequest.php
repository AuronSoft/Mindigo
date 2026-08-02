<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LessonContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'attachment' => [Rule::when($this->routeIs('courses.lessons.attachments.show'), ['required', 'integer', 'min:0'])],
        ];
    }

    public function validationData(): array
    {
        return [...parent::validationData(), 'attachment' => $this->route('attachment')];
    }
}
