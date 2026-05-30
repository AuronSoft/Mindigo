<?php

namespace Mindigo\ClassroomManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;

class ClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('classroom') ? 'classrooms.update' : 'classrooms.create';

        return $this->user()?->hasPermissionTo($permission) ?? false;
    }

    public function rules(): array
    {
        $classroom = $this->route('classroom');

        return [
            'name' => ['required', 'string', 'max:180'],
            'code' => ['required', 'string', 'max:60', Rule::unique('classrooms', 'code')->ignore($classroom?->id)],
            'teacher_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'teacher')],
            'school_year' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:3000'],
            'status' => ['required', Rule::in(Classroom::STATUSES)],
        ];
    }

    public function classroomData(): array
    {
        $data = $this->validated();

        if ($this->user() instanceof User && $this->user()->isTeacher() && !$this->user()->isAdmin()) {
            $data['teacher_id'] = $this->user()->getAuthIdentifier();
        }

        return $data;
    }
}
