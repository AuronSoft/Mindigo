<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\SubjectManagement\Models\SubjectTopic;

class PracticeSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $skill = $this->route('skill');

        return $skill instanceof PracticeSkill
            ? ($this->user()?->can('update', $skill) ?? false)
            : ($this->user()?->can('create', PracticeSkill::class) ?? false);
    }

    public function rules(): array
    {
        $skill = $this->route('skill');

        return [
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')->where('status', 'active')],
            'subject_topic_id' => ['nullable', 'integer', Rule::exists('subject_topics', 'id')->where('status', 'active')],
            'parent_id' => ['nullable', 'integer', Rule::exists('practice_skills', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:80', Rule::unique('practice_skills', 'code')->ignore($skill)],
            'name' => ['required', 'string', 'max:180'],
            'grade_level' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(PracticeSkill::STATUSES)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'question_ids' => ['nullable', 'array', 'max:200'],
            'question_ids.*' => ['integer', 'distinct', Rule::exists('question_bank_questions', 'id')->where('status', 'approved')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $subjectId = $this->integer('subject_id');
            $topicId = $this->integer('subject_topic_id');
            $parentId = $this->integer('parent_id');

            if ($topicId && ! SubjectTopic::query()->whereKey($topicId)->where('subject_id', $subjectId)->exists()) {
                $validator->errors()->add(
                    'subject_topic_id',
                    __('student-practice::app.skills.validation.topic_subject_mismatch')
                );
            }

            if ($parentId && ! PracticeSkill::query()->whereKey($parentId)->where('subject_id', $subjectId)->exists()) {
                $validator->errors()->add(
                    'parent_id',
                    __('student-practice::app.skills.validation.parent_subject_mismatch')
                );
            }

            if ($this->route('skill') instanceof PracticeSkill && $parentId === $this->route('skill')->getKey()) {
                $validator->errors()->add('parent_id', __('student-practice::app.skills.validation.parent_self'));
            }

            if ($this->route('skill') instanceof PracticeSkill && $parentId && $this->createsParentCycle($parentId)) {
                $validator->errors()->add('parent_id', __('student-practice::app.skills.validation.parent_cycle'));
            }

            $subjectName = Subject::query()->whereKey($subjectId)->value('name');
            $questionIds = collect($this->input('question_ids', []))->map(fn ($id) => (int) $id)->filter()->unique();
            if ($subjectName && $questionIds->isNotEmpty() && Question::query()
                ->whereKey($questionIds)
                ->where('subject', '!=', $subjectName)
                ->exists()) {
                $validator->errors()->add('question_ids', __('student-practice::app.skills.validation.question_subject_mismatch'));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => str($this->input('code'))->trim()->upper()->toString(),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }

    private function createsParentCycle(int $parentId): bool
    {
        $skillId = (int) $this->route('skill')->getKey();
        $visited = [];

        while ($parentId > 0 && ! in_array($parentId, $visited, true)) {
            if ($parentId === $skillId) {
                return true;
            }
            $visited[] = $parentId;
            $parentId = (int) PracticeSkill::query()->whereKey($parentId)->value('parent_id');
        }

        return false;
    }
}
