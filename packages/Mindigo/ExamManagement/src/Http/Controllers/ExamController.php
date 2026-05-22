<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamQuestion;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\QuestionBank\Models\QuestionFolder;
use Symfony\Component\HttpFoundation\Response;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizePermission($request->user(), 'exams.view');

        $query = Exam::query()->with('creator:id,name,email,role')->withCount('attempts')->latest('updated_at');

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));
            $query->where(function ($builder) use ($keyword) {
                $builder->where('title', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%")
                    ->orWhere('topic', 'like', "%{$keyword}%");
            });
        }

        foreach (['status', 'subject'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        return view('Mindigo-exam-management::index', [
            'exams' => $query->paginate(12)->withQueryString(),
            'stats' => $this->stats(),
            'statuses' => Exam::STATUSES,
            'subjects' => $this->subjects(),
            'filters' => $request->only(['keyword', 'status', 'subject']),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizePermission($request->user(), 'exams.create');

        return view('Mindigo-exam-management::create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'exams.create');

        $validated = $this->validated($request);

        $exam = DB::transaction(function () use ($request, $validated) {
            $exam = Exam::query()->create([
                ...$validated['exam'],
                'created_by' => $request->user()->getAuthIdentifier(),
                'slug' => $this->uniqueSlug($validated['exam']['title']),
                'status' => 'draft',
                'generation_config' => $validated['generation'],
            ]);

            $this->syncGeneratedQuestions($exam, $validated['generation']);

            return $exam->fresh();
        });

        $this->audit('create', [], $exam->only($this->auditFields()), $exam);

        return redirect()
            ->route('exams.show', $exam)
            ->with('success', __('Mindigo-exam-management::app.messages.created'));
    }

    public function show(Request $request, Exam $exam)
    {
        $this->authorizePermission($request->user(), 'exams.view');
        $exam->load(['creator:id,name,email,role', 'questions', 'attempts.user:id,name,email,role']);

        return view('Mindigo-exam-management::show', [
            'exam' => $exam,
        ]);
    }

    public function edit(Request $request, Exam $exam)
    {
        $this->authorizePermission($request->user(), 'exams.update');
        $exam->load('questions');

        return view('Mindigo-exam-management::edit', array_merge($this->formData(), [
            'exam' => $exam,
        ]));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'exams.update');

        $oldValues = $exam->only($this->auditFields());
        $validated = $this->validated($request, $exam);

        DB::transaction(function () use ($request, $exam, $validated) {
            $exam->fill([
                ...$validated['exam'],
                'generation_config' => $validated['generation'],
            ])->save();

            if ($request->boolean('regenerate_questions')) {
                $this->syncGeneratedQuestions($exam, $validated['generation']);
            }
        });

        $this->audit('update', $oldValues, $exam->fresh()->only($this->auditFields()), $exam);

        return redirect()
            ->route('exams.show', $exam)
            ->with('success', __('Mindigo-exam-management::app.messages.updated'));
    }

    public function publish(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'exams.publish');

        if ($exam->questions()->count() === 0) {
            return back()->with('error', __('Mindigo-exam-management::app.messages.publish_without_questions'));
        }

        $oldValues = $exam->only($this->auditFields());
        $exam->forceFill([
            'status' => 'published',
            'published_at' => now(),
        ])->save();

        $this->audit('publish', $oldValues, $exam->only($this->auditFields()), $exam);

        return back()->with('success', __('Mindigo-exam-management::app.messages.published'));
    }

    public function close(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'exams.publish');

        $oldValues = $exam->only($this->auditFields());
        $exam->forceFill(['status' => 'closed'])->save();

        $this->audit('close', $oldValues, $exam->only($this->auditFields()), $exam);

        return back()->with('success', __('Mindigo-exam-management::app.messages.closed'));
    }

    public function destroy(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'exams.delete');

        $oldValues = $exam->only($this->auditFields());
        $exam->delete();

        $this->audit('delete', $oldValues, [], $exam);

        return redirect()
            ->route('exams.index')
            ->with('success', __('Mindigo-exam-management::app.messages.deleted'));
    }

    private function validated(Request $request, ?Exam $exam = null): array
    {
        $validated = $request->validate([
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
            'folder_id' => ['nullable', 'exists:question_bank_folders,id'],
            'generation_subject' => ['nullable', 'string', 'max:150'],
            'generation_topic' => ['nullable', 'string', 'max:150'],
            'generation_difficulty' => ['nullable', Rule::in(['easy', 'medium', 'hard'])],
            'counts' => ['required', 'array'],
            'counts.single_choice' => ['nullable', 'integer', 'min:0', 'max:200'],
            'counts.multiple_choice' => ['nullable', 'integer', 'min:0', 'max:200'],
            'counts.true_false' => ['nullable', 'integer', 'min:0', 'max:200'],
            'counts.short_answer' => ['nullable', 'integer', 'min:0', 'max:200'],
            'counts.essay' => ['nullable', 'integer', 'min:0', 'max:200'],
            'points' => ['required', 'array'],
            'points.single_choice' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'points.multiple_choice' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'points.true_false' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'points.short_answer' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'points.essay' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $counts = array_map('intval', $validated['counts']);
        if (array_sum($counts) < 1) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, __('Mindigo-exam-management::app.messages.no_generation_count'));
        }

        return [
            'exam' => [
                'title' => $validated['title'],
                'subject' => $validated['subject'] ?? null,
                'topic' => $validated['topic'] ?? null,
                'description' => $validated['description'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'max_attempts' => $validated['max_attempts'],
                'passing_score' => $validated['passing_score'],
                'shuffle_questions' => $request->boolean('shuffle_questions'),
                'shuffle_answers' => $request->boolean('shuffle_answers'),
                'show_results' => $request->boolean('show_results'),
                'audience' => ['roles' => ['student']],
            ],
            'generation' => [
                'folder_id' => $validated['folder_id'] ?? null,
                'subject' => $validated['generation_subject'] ?? null,
                'topic' => $validated['generation_topic'] ?? null,
                'difficulty' => $validated['generation_difficulty'] ?? null,
                'counts' => $counts,
                'points' => array_map(fn ($value) => (float) $value, $validated['points']),
            ],
        ];
    }

    private function syncGeneratedQuestions(Exam $exam, array $config): void
    {
        $selected = collect();

        foreach ($config['counts'] as $type => $count) {
            if ($count < 1) {
                continue;
            }

            $query = Question::query()
                ->where('status', 'approved')
                ->where('type', $type);

            if ($config['folder_id']) {
                $query->where('folder_id', $config['folder_id']);
            }

            foreach (['subject', 'topic', 'difficulty'] as $field) {
                if (!empty($config[$field])) {
                    $query->where($field, $config[$field]);
                }
            }

            $questions = $query->inRandomOrder()->limit($count)->get();

            if ($questions->count() < $count) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, __('Mindigo-exam-management::app.messages.not_enough_questions', [
                    'type' => __('Mindigo-exam-management::app.question_types.' . $type),
                ]));
            }

            $selected = $selected->merge($questions->map(fn (Question $question) => [$question, (float) ($config['points'][$type] ?? 1)]));
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

    private function formData(): array
    {
        return [
            'folders' => QuestionFolder::query()->withCount('questions')->orderBy('name')->get(),
            'subjects' => $this->subjects(),
            'types' => ['single_choice', 'multiple_choice', 'true_false', 'short_answer', 'essay'],
            'difficulties' => ['easy', 'medium', 'hard'],
        ];
    }

    private function stats(): array
    {
        return [
            'total' => Exam::query()->count(),
            'published' => Exam::query()->where('status', 'published')->count(),
            'draft' => Exam::query()->where('status', 'draft')->count(),
            'closed' => Exam::query()->where('status', 'closed')->count(),
        ];
    }

    private function subjects(): array
    {
        return Question::query()
            ->select('subject')
            ->whereNotNull('subject')
            ->distinct()
            ->orderBy('subject')
            ->pluck('subject')
            ->filter()
            ->values()
            ->all();
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

    private function authorizePermission(User $user, string $permission): void
    {
        if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }

    private function auditFields(): array
    {
        return ['id', 'title', 'subject', 'topic', 'status', 'duration_minutes', 'max_attempts', 'passing_score', 'total_questions', 'total_points'];
    }

    private function audit(string $action, array $oldValues, array $newValues, Exam $exam): void
    {
        if (!class_exists(\Mindigo\AuditLog\Services\AuditLogService::class)) {
            return;
        }

        app(\Mindigo\AuditLog\Services\AuditLogService::class)->record(
            $action,
            'exams',
            $oldValues,
            $newValues,
            ['exam_id' => $exam->id, 'title' => $exam->title],
            $exam
        );
    }
}
