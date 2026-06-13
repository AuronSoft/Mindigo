<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Http\Requests\ExamRequest;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamQuestion;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\QuestionBank\Models\QuestionFolder;
use Mindigo\SubjectManagement\Models\Subject;

class ExamService
{
    public function __construct(private ExamAuditService $audit)
    {
    }

    public function create(ExamRequest $request): Exam
    {
        $exam = DB::transaction(function () use ($request): Exam {
            $exam = Exam::query()->create([
                ...$request->examData(),
                'created_by' => $request->user()->getAuthIdentifier(),
                'slug' => $this->uniqueSlug($request->examData()['title']),
                'status' => 'draft',
                'generation_config' => $request->generationConfig(),
            ]);

            $this->syncGeneratedQuestions($exam, $request->generationConfig());

            return $exam->fresh();
        });

        $this->auditExam('create', [], $exam->only($this->auditFields()), $exam);

        return $exam;
    }

    public function update(Exam $exam, ExamRequest $request): Exam
    {
        $oldValues = $exam->only($this->auditFields());

        DB::transaction(function () use ($request, $exam): void {
            $exam->fill([
                ...$request->examData(),
                'generation_config' => $request->generationConfig(),
            ])->save();

            if ($request->shouldRegenerateQuestions()) {
                $this->syncGeneratedQuestions($exam, $request->generationConfig());
            }
        });

        $exam = $exam->fresh();
        $this->auditExam('update', $oldValues, $exam->only($this->auditFields()), $exam);

        return $exam;
    }

    public function publish(Exam $exam): void
    {
        if ($exam->questions()->count() === 0) {
            throw ValidationException::withMessages([
                'exam' => __('Mindigo-exam-management::app.messages.publish_without_questions'),
            ]);
        }

        $oldValues = $exam->only($this->auditFields());
        $exam->forceFill([
            'status' => 'published',
            'published_at' => now(),
        ])->save();

        $this->auditExam('publish', $oldValues, $exam->only($this->auditFields()), $exam);
    }

    public function close(Exam $exam): void
    {
        $oldValues = $exam->only($this->auditFields());
        $exam->forceFill(['status' => 'closed'])->save();

        $this->auditExam('close', $oldValues, $exam->only($this->auditFields()), $exam);
    }

    public function delete(Exam $exam): void
    {
        $oldValues = $exam->only($this->auditFields());
        $exam->delete();

        $this->auditExam('delete', $oldValues, [], $exam);
    }

    public function filteredList(array $filters)
    {
        $query = Exam::query()
            ->with('creator:id,name,email,role')
            ->withCount('attempts')
            ->latest('updated_at');

        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('title', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%")
                    ->orWhere('topic', 'like', "%{$keyword}%");
            });
        }

        foreach (['status', 'subject'] as $filter) {
            if (filled($filters[$filter] ?? null)) {
                $query->where($filter, $filters[$filter]);
            }
        }

        return $query->paginate(12)->withQueryString();
    }

    public function formData(): array
    {
        return [
            'folders' => QuestionFolder::query()->withCount('questions')->orderBy('name')->get(),
            'subjects' => $this->subjects(),
            'subjectTopics' => $this->topicsBySubject(),
            'types' => ExamRequest::TYPES,
            'difficulties' => ExamRequest::DIFFICULTIES,
        ];
    }

    public function stats(): array
    {
        return [
            'total' => Exam::query()->count(),
            'published' => Exam::query()->where('status', 'published')->count(),
            'draft' => Exam::query()->where('status', 'draft')->count(),
            'closed' => Exam::query()->where('status', 'closed')->count(),
        ];
    }

    public function subjects(): array
    {
        if (class_exists(Subject::class)) {
            $subjects = Subject::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->map(fn ($subject) => trim((string) $subject))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($subjects)) {
                return $subjects;
            }
        }

        return Question::query()
            ->select('subject')
            ->whereNotNull('subject')
            ->distinct()
            ->orderBy('subject')
            ->pluck('subject')
            ->map(fn ($subject) => trim((string) $subject))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function topicsBySubject(): array
    {
        if (class_exists(Subject::class)) {
            $subjects = Subject::query()
                ->with(['topics' => fn ($query) => $query
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->orderBy('name')])
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            if ($subjects->isNotEmpty()) {
                return $subjects
                    ->mapWithKeys(fn (Subject $subject) => [
                        trim((string) $subject->name) => $subject->topics
                            ->pluck('name')
                            ->map(fn ($topic) => trim((string) $topic))
                            ->filter()
                            ->unique()
                            ->values()
                            ->all(),
                    ])
                    ->filter(fn (array $topics, string $subject) => filled($subject))
                    ->all();
            }
        }

        return Question::query()
            ->select(['subject', 'topic'])
            ->whereNotNull('subject')
            ->whereNotNull('topic')
            ->orderBy('subject')
            ->orderBy('topic')
            ->get()
            ->map(fn (Question $question) => [
                'subject' => trim((string) $question->subject),
                'topic' => trim((string) $question->topic),
            ])
            ->filter(fn (array $row) => filled($row['subject']) && filled($row['topic']))
            ->groupBy('subject')
            ->map(fn ($rows) => $rows->pluck('topic')->filter()->unique()->values()->all())
            ->all();
    }

    public function can(User $user, string $permission): bool
    {
        return method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission);
    }

    private function syncGeneratedQuestions(Exam $exam, array $config): void
    {
        $selected = collect();

        foreach ($config['counts'] as $type => $count) {
            if ($count < 1) {
                continue;
            }

            $questions = $this->sourceQuestionQuery($type, $config)
                ->inRandomOrder()
                ->limit($count)
                ->get();

            if ($questions->count() < $count) {
                throw ValidationException::withMessages([
                    'counts.' . $type => __('Mindigo-exam-management::app.messages.not_enough_questions', [
                        'type' => __('Mindigo-exam-management::app.question_types.' . $type),
                    ]),
                ]);
            }

            $selected = $selected->merge(
                $questions->map(fn (Question $question) => [$question, (float) ($config['points'][$type] ?? 1)])
            );
        }

        $exam->questions()->delete();

        $sort = 1;
        foreach ($selected->shuffle() as [$question, $points]) {
            ExamQuestion::query()->create([
                'exam_id' => $exam->id,
                'question_id' => $question->id,
                'sort_order' => $sort++,
                'subject' => $question->subject,
                'topic' => $question->topic,
                'type' => $question->type,
                'difficulty' => $question->difficulty,
                'content' => $question->content,
                'options' => $question->options ?? [],
                'correct_answers' => $question->correct_answers ?? [],
                'explanation' => $question->explanation,
                'points' => $points,
            ]);
        }

        $exam->forceFill([
            'total_questions' => $exam->questions()->count(),
            'total_points' => $exam->questions()->sum('points'),
        ])->save();
    }

    private function sourceQuestionQuery(string $type, array $config)
    {
        $query = Question::query()
            ->where('status', 'approved')
            ->where('type', $type);

        if (!empty($config['folder_id'])) {
            $query->where('folder_id', $config['folder_id']);
        }

        foreach (['subject', 'topic', 'difficulty'] as $field) {
            if (!empty($config[$field])) {
                $query->where($field, $config[$field]);
            }
        }

        return $query;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while (Exam::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    private function auditFields(): array
    {
        return ['id', 'title', 'subject', 'topic', 'status', 'duration_minutes', 'max_attempts', 'passing_score', 'total_questions', 'total_points'];
    }

    private function auditExam(string $action, array $oldValues, array $newValues, Exam $exam): void
    {
        $this->audit->record(
            $action,
            'exams',
            $oldValues,
            $newValues,
            ['exam_id' => $exam->id, 'title' => $exam->title],
            $exam
        );
    }
}
