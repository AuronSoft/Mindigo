<?php

namespace Mindigo\ExamManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;
use Mindigo\ExamManagement\Models\ExamSession;

class ExamSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ExamSession::class) === true;
    }

    public function rules(): array
    {
        return [
            'exam_template_version_id' => ['required', 'integer', 'exists:exam_template_versions,id'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'passing_score' => ['required', 'numeric', 'min:0'],
            'result_policy' => ['required', 'in:immediately,after_end,after_release'],
            'shuffle_questions' => ['sometimes', 'boolean'],
            'shuffle_answers' => ['sometimes', 'boolean'],
            'security_policy' => ['sometimes', 'array'],
            'security_policy.fullscreen' => ['sometimes', 'boolean'],
            'security_policy.tab_switch_detection' => ['sometimes', 'boolean'],
            'classroom_ids' => ['required', 'array', 'min:1'],
            'classroom_ids.*' => ['integer', 'distinct', 'exists:classrooms,id'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->filled(['starts_at', 'ends_at', 'duration_minutes'])) {
                return;
            }

            $windowMinutes = Carbon::parse($this->string('starts_at'))->diffInMinutes(Carbon::parse($this->string('ends_at')));
            if ($this->integer('duration_minutes') > $windowMinutes) {
                $validator->errors()->add('duration_minutes', __('Mindigo-exam-management::app.session_builder.duration_exceeds_window'));
            }
        }];
    }
}
