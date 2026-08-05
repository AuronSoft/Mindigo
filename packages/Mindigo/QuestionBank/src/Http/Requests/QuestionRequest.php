<?php

namespace Mindigo\QuestionBank\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\SubjectManagement\Models\SubjectTopic;

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
            'grade_level' => ['nullable', 'string', 'max:40'],
            'estimated_seconds' => ['nullable', 'integer', 'min:10', 'max:7200'],
            'hint' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $subjects = $this->activeSubjectsWithTopics();

            if (empty($subjects)) {
                return;
            }

            $subject = trim((string) $this->input('subject'));
            $topic = trim((string) $this->input('topic'));

            if (! array_key_exists($subject, $subjects)) {
                $validator->errors()->add('subject', __('Mindigo-question-bank::app.validation.subject_required_from_catalog'));

                return;
            }

            if ($topic !== '' && ! in_array($topic, $subjects[$subject], true)) {
                $validator->errors()->add('topic', __('Mindigo-question-bank::app.validation.topic_must_match_subject'));
            }
        });
    }

    public function questionData(?Question $question = null): array
    {
        $validated = $this->validated();
        $options = $this->cleanArray($validated['options'] ?? []);
        $correctAnswers = $this->answersFor($validated['type'], $validated, $options);
        $subject = Subject::query()->where('name', $validated['subject'])->first();
        $topic = $subject && filled($validated['topic'] ?? null)
            ? SubjectTopic::query()
                ->where('subject_id', $subject->getKey())
                ->where('name', $validated['topic'])
                ->first()
            : null;

        if (! in_array($validated['type'], ['short_answer', 'essay'], true) && count($options) < 2) {
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
            'subject_id' => $subject?->getKey(),
            'subject_topic_id' => $topic?->getKey(),
            'grade_level' => $validated['grade_level'] ?? null,
            'estimated_seconds' => $validated['estimated_seconds'] ?? null,
            'hint' => $validated['hint'] ?? null,
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

    private function activeSubjectsWithTopics(): array
    {
        if (! class_exists(Subject::class)) {
            return [];
        }

        return Subject::query()
            ->with(['topics' => fn ($query) => $query
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Subject $subject) => [
                $subject->name => $subject->topics->pluck('name')->values()->all(),
            ])
            ->all();
    }
}
