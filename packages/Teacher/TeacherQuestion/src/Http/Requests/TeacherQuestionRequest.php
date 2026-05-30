<?php

namespace Mindigo\TeacherQuestion\Http\Requests;

use Mindigo\QuestionBank\Http\Requests\QuestionRequest;

class TeacherQuestionRequest extends QuestionRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
