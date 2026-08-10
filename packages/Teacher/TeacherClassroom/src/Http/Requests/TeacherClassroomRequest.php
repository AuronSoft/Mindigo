<?php

namespace Mindigo\TeacherClassroom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherClassroom\Models\Classroom;

class TeacherClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route đã được bảo vệ bởi middleware role:teacher|admin
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $classroom = $this->route('classroom');

        return [
            'name' => ['required', 'string', 'max:180'],
            'code' => ['required', 'string', 'max:60', Rule::unique('classrooms', 'code')->ignore($classroom?->id)],
            'school_year' => ['required', Rule::in(array_keys(Classroom::schoolYearOptions()))],
            'description' => ['nullable', 'string', 'max:3000'],
            'status' => ['required', Rule::in(Classroom::STATUSES)],
            'assistant_id' => [
                'nullable',
                'integer',
                Rule::notIn([(int) $this->user()?->getAuthIdentifier()]),
                Rule::exists('users', 'id')
                    ->where('role', 'teacher')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'type' => ['required', Rule::in(Classroom::TYPES)],
            'course_id' => [
                'nullable',
                'required_if:type,'.Classroom::TYPE_COURSE,
                'prohibited_unless:type,'.Classroom::TYPE_COURSE,
                Rule::exists('courses', 'id')
                    ->where('teacher_id', $this->user()?->getAuthIdentifier())
                    ->where('publication_status', 'published')
                    ->where('is_active', true)
                    ->whereNotNull('subject_id')
                    ->whereNull('deleted_at'),
            ],
            'subject_id' => [
                'nullable',
                'required_if:type,'.Classroom::TYPE_STANDALONE,
                'prohibited_unless:type,'.Classroom::TYPE_STANDALONE,
                Rule::exists('subjects', 'id')->where('status', 'active')->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * Dữ liệu lớp học — teacher_id luôn bị ép về chính giáo viên đang đăng nhập.
     */
    public function classroomData(): array
    {
        return [
            ...$this->validated(),
            'teacher_id' => $this->user()->getAuthIdentifier(),
        ];
    }
}
