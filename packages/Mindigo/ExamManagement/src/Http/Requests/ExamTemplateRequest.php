<?php

namespace Mindigo\ExamManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\ExamManagement\Models\ExamTemplate;

class ExamTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $template = $this->route('template');

        return $template instanceof ExamTemplate
            ? $this->user()->can('update', $template)
            : $this->user()->can('create', ExamTemplate::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'topic' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'settings' => ['sometimes', 'array'],
            'settings.shuffle_questions' => ['sometimes', 'boolean'],
            'settings.shuffle_answers' => ['sometimes', 'boolean'],
            'sections' => ['required', 'array', 'min:1', 'max:20'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.instructions' => ['nullable', 'string', 'max:2000'],
            'sections.*.shuffle_questions' => ['sometimes', 'boolean'],
            'sections.*.questions' => ['required', 'array', 'min:1', 'max:200'],
            'sections.*.questions.*.id' => ['required', 'integer', 'distinct', Rule::exists('question_bank_questions', 'id')],
            'sections.*.questions.*.points' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'sections.*.questions.*.rubric_json' => ['nullable', 'json', 'max:10000'],
        ];
    }
}
