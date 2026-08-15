<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherClassroom\Models\Classroom;

class PersonalizedPracticeSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! ($user?->hasPermissionTo('learning-tools.use') ?? false)) {
            return false;
        }
        if (! $this->filled('classroom_id')) {
            return true;
        }

        return $user->isTeacher()
            && Classroom::query()->whereKey($this->integer('classroom_id'))->where('teacher_id', $user->getAuthIdentifier())->exists();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'subject' => ['nullable', 'string', 'max:120'],
            'topic' => ['nullable', 'string', 'max:180'],
            'difficulty' => ['nullable', Rule::in(['easy', 'medium', 'hard'])],
            'source' => ['required', Rule::in(['manual', 'weak_topics', 'mistakes'])],
            'question_count' => ['required', 'integer', 'min:1', 'max:50'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
        ];
    }
}
