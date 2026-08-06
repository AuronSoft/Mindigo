<?php

namespace Mindigo\TeacherOnboarding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherOnboarding\Models\TeacherApplicationInterview;

class TeacherApplicationInterviewEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $interview = $this->route('interview');

        return $interview instanceof TeacherApplicationInterview
            && ($this->user()?->can('update', $interview->application) ?? false);
    }

    public function rules(): array
    {
        return [
            'subject_knowledge_score' => ['required', 'integer', 'min:1', 'max:10'],
            'pedagogy_score' => ['required', 'integer', 'min:1', 'max:10'],
            'communication_score' => ['required', 'integer', 'min:1', 'max:10'],
            'lms_technology_score' => ['required', 'integer', 'min:1', 'max:10'],
            'overall_comment' => ['required', 'string', 'max:3000'],
            'result' => ['required', Rule::in(TeacherApplicationInterview::RESULTS)],
        ];
    }
}
