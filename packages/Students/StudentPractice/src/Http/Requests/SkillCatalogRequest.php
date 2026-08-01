<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SkillCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['student', 'admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:120'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'grade_level' => ['nullable', 'string', 'max:40'],
        ];
    }
}
