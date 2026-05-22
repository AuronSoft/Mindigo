<?php

namespace Mindigo\QuestionBank\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mindigo\QuestionBank\Models\Question;

class QuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('question') ? 'questions.update' : 'questions.create';

        return $this->user()?->hasPermissionTo($permission) ?? false;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:150'],
            'folder_id' => ['nullable', 'exists:question_bank_folders,id'],
            'topic' => ['nullable', 'string', 'max:150'],
            'type' => ['required', Rule::in(Question::TYPES)],
            'difficulty' => ['required', Rule::in(Question::DIFFICULTIES)],
            'status' => ['nullable', Rule::in(['draft', 'reviewing'])],
            'content' => ['required', 'string', 'max:5000'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string', 'max:1000'],
            'correct_answers' => ['nullable', 'array'],
            'correct_answers.*' => ['nullable', 'string', 'max:1000'],
            'correct_answer_single' => ['nullable', 'string', 'max:1000'],
            'short_answer_text' => ['nullable', 'string', 'max:1000'],
            'explanation' => ['nullable', 'string', 'max:5000'],
            'tags_text' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function questionData(?Question $question = null): array
    {
        $validated = $this->validated();
        $options = $this->cleanArray($validated['options'] ?? []);
        $correctAnswers = $this->answersFor($validated['type'], $validated, $options);

        if (!in_array($validated['type'], ['short_answer', 'essay'], true) && count($options) < 2) {
            throw ValidationException::withMessages([
                'options' => __('Mindigo-question-bank::app.validation.options_required'),
            ]);
        }

        if ($validated['type'] !== 'essay' && empty($correctAnswers)) {
            throw ValidationException::withMessages([
                'correct_answers' => __('Mindigo-question-bank::app.validation.correct_answer_required'),
            ]);
        }

        return [
            'subject' => $validated['subject'],
            'folder_id' => $validated['folder_id'] ?? null,
            'topic' => $validated['topic'] ?? null,
            'type' => $validated['type'],
            'difficulty' => $validated['difficulty'],
            'status' => $this->input('submit_for_review') ? 'reviewing' : ($validated['status'] ?? $question?->status ?? 'draft'),
            'content' => $validated['content'],
            'options' => in_array($validated['type'], ['short_answer', 'essay'], true) ? [] : $options,
            'correct_answers' => $correctAnswers,
            'explanation' => $validated['explanation'] ?? null,
            'tags' => $this->csv($validated['tags_text'] ?? ''),
        ];
    }

    private function answersFor(string $type, array $validated, array $options): array
    {
        if ($type === 'essay') {
            return [];
        }

        if ($type === 'short_answer') {
            return $this->cleanArray(preg_split('/\R/', (string) ($validated['short_answer_text'] ?? '')) ?: []);
        }

        if ($type === 'single_choice' || $type === 'true_false') {
            $answer = trim((string) ($validated['correct_answer_single'] ?? ''));

            return $answer !== '' ? [$answer] : [];
        }

        return array_values(array_intersect($this->cleanArray($validated['correct_answers'] ?? []), $options));
    }

    private function cleanArray(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    private function csv(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();
    }
}
