<?php

namespace Mindigo\TeacherClassroom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\ClassroomManagement\Models\Classroom;

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
            'school_year' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:3000'],
            'status' => ['required', Rule::in(Classroom::STATUSES)],
            'assistant_id' => ['nullable', 'integer', 'exists:users,id'],
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
