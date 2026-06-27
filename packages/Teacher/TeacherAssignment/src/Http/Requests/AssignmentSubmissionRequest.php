<?php

namespace Mindigo\TeacherAssignment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignmentSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxScore = optional($this->route('assignment'))->max_score ?? 10;

        return [
            'score' => "required|numeric|min:0|max:{$maxScore}",
            'feedback' => 'nullable|string|max:2000',
            'status' => 'required|in:graded,returned',
        ];
    }

    public function messages(): array
    {
        return [
            'score.required' => 'Vui lòng nhập điểm.',
            'score.min' => 'Điểm không được âm.',
            'score.max' => 'Điểm vượt quá điểm tối đa.',
        ];
    }
}
