<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcademicScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('learning-tools.use') ?? false;
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:180'], 'type' => ['required', Rule::in(['primary_secondary', 'subject_semester', 'school_year', 'grade_10_admission', 'university_admission', 'custom'])], 'items' => ['required', 'array', 'min:1', 'max:20'], 'items.*.name' => ['nullable', 'string', 'max:120'], 'items.*.score' => ['nullable', 'numeric', 'min:0', 'max:100'], 'items.*.weight' => ['nullable', 'numeric', 'min:0.01', 'max:100'], 'bonus_score' => ['nullable', 'numeric', 'min:0', 'max:30']];
    }
}
