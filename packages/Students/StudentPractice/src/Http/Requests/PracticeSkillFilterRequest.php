<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\StudentPractice\Models\PracticeSkill;

class PracticeSkillFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', PracticeSkill::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:120'],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'status' => ['nullable', 'string', Rule::in(PracticeSkill::STATUSES)],
        ];
    }
}
