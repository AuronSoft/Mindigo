<?php

namespace Mindigo\TeacherExam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;

class GradeExamAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.points' => ['required', 'numeric', 'min:0'],
            'grades.*.feedback' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $attempt = $this->route('attempt');
            $answers = ExamAttemptAnswer::query()
                ->where('exam_attempt_id', $attempt->id)
                ->with('question:id,points')
                ->whereIn('id', array_keys($this->input('grades', [])))
                ->get()
                ->keyBy('id');

            foreach ($this->input('grades', []) as $answerId => $grade) {
                $answer = $answers->get((int) $answerId);
                if (! $answer) {
                    $validator->errors()->add("grades.{$answerId}", __('teacher-exam::app.invalid_grade_answer'));

                    continue;
                }

                if ((float) $grade['points'] > (float) $answer->question->points) {
                    $validator->errors()->add("grades.{$answerId}.points", __('teacher-exam::app.grade_exceeds_points'));
                }
            }
        });
    }
}
