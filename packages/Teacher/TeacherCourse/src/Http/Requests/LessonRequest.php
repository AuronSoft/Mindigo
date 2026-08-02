<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\Lesson;

class LessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lesson = $this->route('lesson');
        if ($lesson instanceof Lesson) {
            return $this->user()?->can('update', $lesson) ?? false;
        }

        $course = $this->route('course');

        return $course instanceof Course && ($this->user()?->can('update', $course) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_preview' => ['nullable', 'boolean'],
            'content' => ['nullable', 'string'],
            'video' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:512000'],
            'remove_video' => ['nullable', 'boolean'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
            'remove_attachments' => ['nullable', 'array'],
            'remove_attachments.*' => ['string'],
            'assignment_id' => ['nullable', 'integer', 'exists:assignments,id'],
            'prerequisite_lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $course = $this->course();
            if (! $course) {
                return;
            }

            if ($assignmentId = $this->integer('assignment_id')) {
                $validAssignment = Assignment::query()
                    ->whereKey($assignmentId)
                    ->where('teacher_id', $course->teacher_id)
                    ->where('status', 'published')
                    ->exists();

                if (! $validAssignment) {
                    $validator->errors()->add('assignment_id', __('teacher-course::app.invalid_assignment_scope'));
                }
            }

            if ($prerequisiteId = $this->integer('prerequisite_lesson_id')) {
                $editingLesson = $this->route('lesson');
                $validPrerequisite = Lesson::query()
                    ->whereKey($prerequisiteId)
                    ->whereHas('chapter', fn ($query) => $query->where('course_id', $course->getKey()))
                    ->when($editingLesson instanceof Lesson, fn ($query) => $query->whereKeyNot($editingLesson))
                    ->exists();

                if (! $validPrerequisite) {
                    $validator->errors()->add('prerequisite_lesson_id', __('teacher-course::app.invalid_prerequisite_scope'));
                }
            }
        });
    }

    private function course(): ?Course
    {
        $course = $this->route('course');
        if ($course instanceof Course) {
            return $course;
        }

        $lesson = $this->route('lesson');

        return $lesson instanceof Lesson ? $lesson->chapter->course : null;
    }
}
