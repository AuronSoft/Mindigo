<?php

namespace Mindigo\StudentAssignment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Mindigo\TeacherAssignment\Models\Assignment;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Assignment|null $assignment */
        $assignment = $this->route('assignment');
        $fileRules = ['nullable', 'file', 'mimes:pdf,doc,docx,zip,rar,xls,xlsx,ppt,pptx,jpg,jpeg,png', 'max:20480'];
        $textRules = ['nullable', 'string', 'max:50000'];

        if ($assignment?->submission_type === 'both') {
            return [
                'submission_method' => ['required', Rule::in(['file', 'text'])],
                'submission_file' => $fileRules,
                'text_content' => $textRules,
            ];
        }

        if ($assignment?->allowsFile()) {
            return [
                'submission_file' => ['required', ...array_slice($fileRules, 1)],
                'text_content' => ['nullable'],
            ];
        }

        return [
            'submission_file' => ['nullable'],
            'text_content' => ['required', ...array_slice($textRules, 1)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Assignment|null $assignment */
            $assignment = $this->route('assignment');

            if (! $assignment) {
                return;
            }

            $submission = $assignment->submissions()->where('student_id', $this->user()?->id)->first();
            if ($submission) {
                $validator->errors()->add('submission', __('student-assignment::app.errors.already_submitted'));

                return;
            }

            if ($assignment->submission_type !== 'both') {
                return;
            }

            $method = $this->input('submission_method');
            $hasFile = $this->hasFile('submission_file');
            $hasText = filled(trim((string) $this->input('text_content')));

            if ($method === 'file') {
                if (! $hasFile) {
                    $validator->errors()->add('submission_file', __('student-assignment::app.validation.file_required'));
                }
                if ($hasText) {
                    $validator->errors()->add('text_content', __('student-assignment::app.validation.only_one_method'));
                }

                return;
            }

            if ($method === 'text') {
                if (! $hasText) {
                    $validator->errors()->add('text_content', __('student-assignment::app.validation.text_required'));
                }
                if ($hasFile) {
                    $validator->errors()->add('submission_file', __('student-assignment::app.validation.only_one_method'));
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'submission_method.required' => __('student-assignment::app.validation.method_required'),
            'submission_file.required' => __('student-assignment::app.validation.file_required'),
            'submission_file.mimes' => __('student-assignment::app.validation.file_mimes'),
            'submission_file.max' => __('student-assignment::app.validation.file_max'),
            'text_content.required' => __('student-assignment::app.validation.text_required'),
            'text_content.max' => __('student-assignment::app.validation.text_max'),
        ];
    }
}
