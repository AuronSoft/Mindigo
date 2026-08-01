<?php

namespace Mindigo\TeacherExam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;

class GradeExamAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Exam|null $exam */
        $exam = $this->route('exam');
        /** @var ExamAttempt|null $attempt */
        $attempt = $this->route('attempt');
        $user = $this->user();

        return $user !== null
            && $exam !== null
            && $attempt !== null
            && (int) $attempt->exam_id === (int) $exam->id
            && in_array($attempt->status, ['submitted', 'expired'], true)
            && ($user->isAdmin() || (int) $exam->created_by === (int) $user->getAuthIdentifier());
    }

    public function rules(): array
    {
        return [
            'grades' => ['required', 'array', 'min:1'],
            'grading_version' => ['required', 'integer', 'min:0'],
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
