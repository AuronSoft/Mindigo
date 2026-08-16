<?php

namespace Mindigo\ExamManagement\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class ExamRequest extends FormRequest
{
    public const TYPES = ['single_choice', 'multiple_choice', 'true_false', 'short_answer', 'essay'];

    public const DIFFICULTIES = ['easy', 'medium', 'hard'];

    public function authorize(): bool
    {
        $permission = $this->route('exam') ? 'exams.update' : 'exams.create';

        return $this->user()?->hasPermissionTo($permission) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:150'],
            'topic' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:3000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'passing_score' => ['required', 'numeric', 'min:0', 'max:999'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'shuffle_answers' => ['nullable', 'boolean'],
            'show_results' => ['nullable', 'boolean'],
            'classroom_ids' => ['required', 'array', 'min:1'],
            'classroom_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('classrooms', 'id')->where(fn ($query) => $query
                    ->where('teacher_id', $this->user()?->getAuthIdentifier())
                    ->where('status', 'active')
                    ->whereNull('deleted_at')),
            ],
            'folder_id' => ['nullable', 'exists:question_bank_folders,id'],
            'generation_subject' => ['nullable', 'string', 'max:150'],
            'generation_topic' => ['nullable', 'string', 'max:150'],
            'generation_difficulty' => ['nullable', Rule::in(self::DIFFICULTIES)],
            'counts' => ['required', 'array'],
            'points' => ['required', 'array'],
            ...$this->typeRules('counts', ['nullable', 'integer', 'min:0', 'max:200']),
            ...$this->typeRules('points', ['nullable', 'numeric', 'min:0', 'max:100']),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'starts_at' => $this->normalizeLocalizedDateTime($this->input('starts_at')),
            'ends_at' => $this->normalizeLocalizedDateTime($this->input('ends_at')),
        ]);
    }

    public function messages(): array
    {
        return [
            'title.required' => __('Mindigo-exam-management::app.validation.title_required'),
            'duration_minutes.required' => __('Mindigo-exam-management::app.validation.duration_required'),
            'duration_minutes.min' => __('Mindigo-exam-management::app.validation.duration_min'),
            'max_attempts.required' => __('Mindigo-exam-management::app.validation.attempts_required'),
            'passing_score.required' => __('Mindigo-exam-management::app.validation.passing_score_required'),
            'classroom_ids.required' => __('Mindigo-exam-management::app.validation.classroom_required'),
            'classroom_ids.min' => __('Mindigo-exam-management::app.validation.classroom_required'),
            'counts.required' => __('Mindigo-exam-management::app.validation.question_structure_required'),
            'starts_at.date' => __('Mindigo-exam-management::app.validation.datetime_format'),
            'ends_at.date' => __('Mindigo-exam-management::app.validation.datetime_format'),
            'ends_at.after_or_equal' => __('Mindigo-exam-management::app.validation.ends_after_start'),
            '*.string' => __('Mindigo-exam-management::app.validation.string'),
            '*.integer' => __('Mindigo-exam-management::app.validation.integer'),
            '*.numeric' => __('Mindigo-exam-management::app.validation.numeric'),
            '*.array' => __('Mindigo-exam-management::app.validation.array'),
            '*.boolean' => __('Mindigo-exam-management::app.validation.boolean'),
            '*.max' => __('Mindigo-exam-management::app.validation.max'),
            '*.exists' => __('Mindigo-exam-management::app.validation.exists'),
            '*.distinct' => __('Mindigo-exam-management::app.validation.distinct'),
            '*.in' => __('Mindigo-exam-management::app.validation.in'),
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => __('Mindigo-exam-management::app.title_field'),
            'subject' => __('Mindigo-exam-management::app.subject'),
            'topic' => __('Mindigo-exam-management::app.topic'),
            'description' => __('Mindigo-exam-management::app.description'),
            'duration_minutes' => __('Mindigo-exam-management::app.duration_minutes'),
            'starts_at' => __('Mindigo-exam-management::app.starts_at'),
            'ends_at' => __('Mindigo-exam-management::app.ends_at'),
            'max_attempts' => __('Mindigo-exam-management::app.max_attempts'),
            'passing_score' => __('Mindigo-exam-management::app.passing_score'),
            'classroom_ids' => __('Mindigo-exam-management::app.assigned_classrooms'),
            'counts' => __('Mindigo-exam-management::app.question_structure'),
            'points' => __('Mindigo-exam-management::app.points'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function () use ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (array_sum($this->generationCounts()) < 1) {
                throw ValidationException::withMessages([
                    'counts' => __('Mindigo-exam-management::app.messages.no_generation_count'),
                ]);
            }
        });
    }

    public function examData(): array
    {
        $validated = $this->validated();

        return [
            'title' => $validated['title'],
            'subject' => $validated['subject'] ?? null,
            'topic' => $validated['topic'] ?? null,
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'max_attempts' => $validated['max_attempts'],
            'passing_score' => $validated['passing_score'],
            'shuffle_questions' => $this->boolean('shuffle_questions'),
            'shuffle_answers' => $this->boolean('shuffle_answers'),
            'show_results' => $this->boolean('show_results'),
            'audience' => [
                'roles' => ['student'],
                'classrooms' => collect($validated['classroom_ids'])->map(fn ($id) => (int) $id)->values()->all(),
            ],
        ];
    }

    public function generationConfig(): array
    {
        $validated = $this->validated();

        return [
            'folder_id' => $validated['folder_id'] ?? null,
            'subject' => $validated['generation_subject'] ?? null,
            'topic' => $validated['generation_topic'] ?? null,
            'difficulty' => $validated['generation_difficulty'] ?? null,
            'counts' => $this->generationCounts(),
            'points' => collect($validated['points'] ?? [])
                ->only(self::TYPES)
                ->map(fn ($value) => (float) $value)
                ->all(),
        ];
    }

    public function shouldRegenerateQuestions(): bool
    {
        return $this->boolean('regenerate_questions');
    }

    public function shouldSubmitForReview(): bool
    {
        return $this->boolean('submit_for_review');
    }

    private function generationCounts(): array
    {
        return collect($this->validated('counts', []))
            ->only(self::TYPES)
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    private function typeRules(string $field, array $rules): array
    {
        return collect(self::TYPES)
            ->mapWithKeys(fn ($type) => ["{$field}.{$type}" => $rules])
            ->all();
    }

    private function normalizeLocalizedDateTime(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        foreach (['d/m/Y H:i', 'Y-m-d\\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value))->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                // Keep trying supported formats before returning the original
                // value so Laravel can produce the localized validation error.
            }
        }

        return $value;
    }
}
