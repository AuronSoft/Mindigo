<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;

class ChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $chapter = $this->route('chapter');
        if ($chapter instanceof Chapter) {
            return $this->user()?->can('update', $chapter) ?? false;
        }

        $course = $this->route('course');

        return $course instanceof Course && ($this->user()?->can('update', $course) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
