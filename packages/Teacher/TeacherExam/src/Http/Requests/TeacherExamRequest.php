<?php

namespace Mindigo\TeacherExam\Http\Requests;

use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Http\Requests\ExamRequest;
use Illuminate\Validation\Validator;

class TeacherExamRequest extends ExamRequest
{
    /**
     * Teacher không cần permission riêng — route đã có middleware role:teacher|admin.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);

        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->has('classroom_ids') || $this->user()?->isAdmin()) {
                return;
            }

            $selectedIds = collect($this->input('classroom_ids', []))->map(fn ($id) => (int) $id)->unique();
            $ownedCount = Classroom::query()
                ->whereIn('id', $selectedIds)
                ->where('teacher_id', $this->user()->getAuthIdentifier())
                ->count();

            if ($ownedCount !== $selectedIds->count()) {
                $validator->errors()->add('classroom_ids', __('teacher-exam::app.invalid_classrooms'));
            }
        });
    }
}
