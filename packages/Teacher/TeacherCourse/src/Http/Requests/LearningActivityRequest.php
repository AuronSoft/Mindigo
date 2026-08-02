<?php

namespace Mindigo\TeacherCourse\Http\Requests;

class LearningActivityRequest extends StudentCourseRequest
{
    public function rules(): array
    {
        return ['seconds' => ['required', 'integer', 'min:1', 'max:3600']];
    }
}
