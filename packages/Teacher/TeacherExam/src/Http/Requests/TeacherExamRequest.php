<?php

namespace Mindigo\TeacherExam\Http\Requests;

use Mindigo\ExamManagement\Http\Requests\ExamRequest;

class TeacherExamRequest extends ExamRequest
{
    /**
     * Teacher không cần permission riêng — route đã có middleware role:teacher|admin.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
