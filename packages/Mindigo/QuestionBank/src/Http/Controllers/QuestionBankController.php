<?php

namespace Mindigo\QuestionBank\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\QuestionBank\Models\QuestionFolder;
use Symfony\Component\HttpFoundation\Response;

class QuestionBankController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizePermission($request->user(), 'questions.view');

        $query = Question::query()->with(['creator:id,name,email,role', 'reviewer:id,name,email,role', 'folder:id,name,color'])->latest('updated_at');

        if (!$request->user()->isAdmin()) {
            $query->where('created_by', $request->user()->getAuthIdentifier());
        }

        if ($request->filled('folder_id')) {
            if ($request->input('folder_id') === 'none') {
                $query->whereNull('folder_id');
            } else {
                $query->where('folder_id', $request->integer('folder_id'));
            }
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));
            $query->where(function ($builder) use ($keyword) {
                $builder->where('content', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%")
                    ->orWhere('topic', 'like', "%{$keyword}%");
            });
        }

        foreach (['subject', 'type', 'difficulty', 'status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        return view('Mindigo-question-bank::index', [
            'questions' => $query->paginate(12)->withQueryString(),
            'stats' => $this->stats($request->user()),
            'types' => Question::TYPES,
            'difficulties' => Question::DIFFICULTIES,
            'statuses' => Question::STATUSES,
            'subjects' => $this->subjects(),
            'folders' => $this->foldersFor($request->user()),
            'currentFolderId' => $request->input('folder_id'),
            'filters' => $request->only(['keyword', 'subject', 'type', 'difficulty', 'status', 'folder_id']),
        ]);
    }

    public function storeFolder(Request $request): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'questions.create');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'subject' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', Rule::in(['green', 'sky', 'amber', 'rose', 'slate'])],
        ]);

        $folder = QuestionFolder::query()->create([
            ...$validated,
            'created_by' => $request->user()->getAuthIdentifier(),
        ]);

        $this->auditFolder('create', [], $folder->only(['id', 'name', 'subject', 'description', 'color']), $folder);

        return redirect()
            ->route('question-bank.index', ['folder_id' => $folder->id])
            ->with('success', __('Mindigo-question-bank::app.messages.folder_created'));
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'questions.create');

        $validated = $request->validate([
            'import_file' => ['required', 'file', 'max:5120', 'extensions:csv,txt,json'],
            'folder_id' => ['nullable', 'exists:question_bank_folders,id'],
            'status' => ['required', Rule::in(['draft', 'reviewing'])],
        ]);

        $file = $request->file('import_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = $extension === 'json'
            ? $this->parseJsonImport((string) file_get_contents($file->getRealPath()))
            : $this->parseCsvImport($file->getRealPath());

        if (empty($rows)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_empty'),
            ]);
        }

        $created = 0;
        $defaultFolderId = $validated['folder_id'] ?? null;

        foreach ($rows as $index => $row) {
            $questionData = $this->questionDataFromImportRow($row, $request->user(), $validated['status'], $defaultFolderId, $index + 2);
            $question = Question::query()->create($questionData);
            $this->audit('import', [], $question->only($this->auditFields()), $question);
            $created++;
        }

        return redirect()
            ->route('question-bank.index', $defaultFolderId ? ['folder_id' => $defaultFolderId] : [])
            ->with('success', trans_choice('Mindigo-question-bank::app.messages.imported', $created, ['count' => $created]));
    }

    public function create(Request $request)
    {
        $this->authorizePermission($request->user(), 'questions.create');

        return view('Mindigo-question-bank::create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'questions.create');

        $validated = $this->validated($request);
        $validated['created_by'] = $request->user()->getAuthIdentifier();
        $validated['status'] = $request->input('submit_for_review') ? 'reviewing' : ($validated['status'] ?? 'draft');

        $question = Question::query()->create($validated);

        $this->audit('create', [], $question->only($this->auditFields()), $question);

        return redirect()
            ->route('question-bank.show', $question)
            ->with('success', __('Mindigo-question-bank::app.messages.created'));
    }

    public function show(Request $request, Question $question)
    {
        $this->authorizeQuestion($request, $question, 'questions.view');
        $question->load(['creator:id,name,email,role', 'reviewer:id,name,email,role', 'folder:id,name,color']);

        return view('Mindigo-question-bank::show', [
            'question' => $question,
            'statuses' => Question::STATUSES,
        ]);
    }

    public function edit(Request $request, Question $question)
    {
        $this->authorizeQuestion($request, $question, 'questions.update');

        return view('Mindigo-question-bank::edit', array_merge($this->formData(), [
            'question' => $question,
        ]));
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $this->authorizeQuestion($request, $question, 'questions.update');

        $oldValues = $question->only($this->auditFields());
        $validated = $this->validated($request, $question);
        $validated['status'] = $request->input('submit_for_review') ? 'reviewing' : ($validated['status'] ?? $question->status);

        $question->fill($validated)->save();

        $this->audit('update', $oldValues, $question->only($this->auditFields()), $question);

        return redirect()
            ->route('question-bank.show', $question)
            ->with('success', __('Mindigo-question-bank::app.messages.updated'));
    }

    public function review(Request $request, Question $question): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'questions.review');

        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'reviewing'])],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldValues = $question->only(['status', 'review_note', 'reviewed_by', 'reviewed_at']);
        $question->forceFill([
            'status' => $validated['status'],
            'review_note' => $validated['review_note'] ?? null,
            'reviewed_by' => $request->user()->getAuthIdentifier(),
            'reviewed_at' => now(),
        ])->save();

        $this->audit('review', $oldValues, $question->only(['status', 'review_note', 'reviewed_by', 'reviewed_at']), $question);

        return back()->with('success', __('Mindigo-question-bank::app.messages.reviewed'));
    }

    public function destroy(Request $request, Question $question): RedirectResponse
    {
        $this->authorizeQuestion($request, $question, 'questions.delete');

        $oldValues = $question->only($this->auditFields());
        $question->delete();

        $this->audit('delete', $oldValues, [], $question);

        return redirect()
            ->route('question-bank.index')
            ->with('success', __('Mindigo-question-bank::app.messages.deleted'));
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
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
        ]);

        $options = $this->cleanArray($validated['options'] ?? []);
        $correctAnswers = $this->answersFor($validated['type'], $validated, $options);

        if ($validated['type'] !== 'short_answer' && count($options) < 2) {
            throw ValidationException::withMessages([
                'options' => __('Mindigo-question-bank::app.validation.options_required'),
            ]);
        }

        if (empty($correctAnswers)) {
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
            'status' => $validated['status'] ?? 'draft',
            'content' => $validated['content'],
            'options' => $validated['type'] === 'short_answer' ? [] : $options,
            'correct_answers' => $correctAnswers,
            'explanation' => $validated['explanation'] ?? null,
            'tags' => $this->csv($validated['tags_text'] ?? ''),
        ];
    }

    private function formData(): array
    {
        return [
            'types' => Question::TYPES,
            'difficulties' => Question::DIFFICULTIES,
            'subjects' => $this->subjects(),
            'folders' => $this->foldersFor(request()->user()),
        ];
    }

    private function stats(User $user): array
    {
        $query = Question::query();

        if (!$user->isAdmin()) {
            $query->where('created_by', $user->getAuthIdentifier());
        }

        return [
            'total' => (clone $query)->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'reviewing' => (clone $query)->where('status', 'reviewing')->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
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

    private function foldersFor(User $user)
    {
        $query = QuestionFolder::query()
            ->withCount('questions')
            ->orderBy('name');

        if (!$user->isAdmin()) {
            $query->where('created_by', $user->getAuthIdentifier());
        }

        return $query->get();
    }

    private function parseJsonImport(string $contents): array
    {
        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_invalid_json'),
            ]);
        }

        return array_is_list($decoded) ? $decoded : ($decoded['questions'] ?? []);
    }

    private function parseCsvImport(string $path): array
    {
        $handle = fopen($path, 'rb');

        if (!$handle) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_unreadable'),
            ]);
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);

            return [];
        }

        $headers = array_map(fn ($header) => trim(strtolower((string) $header)), $headers);
        $rows = [];

        while (($values = fgetcsv($handle)) !== false) {
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $rows[] = array_combine($headers, array_pad($values, count($headers), '')) ?: [];
        }

        fclose($handle);

        return $rows;
    }

    private function questionDataFromImportRow(array $row, User $user, string $defaultStatus, ?int $defaultFolderId, int $rowNumber): array
    {
        $type = trim((string) ($row['type'] ?? 'single_choice'));
        $difficulty = trim((string) ($row['difficulty'] ?? 'medium'));
        $subject = trim((string) ($row['subject'] ?? ''));
        $content = trim((string) ($row['content'] ?? $row['question'] ?? ''));

        if ($subject === '' || $content === '') {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_missing_required', ['row' => $rowNumber]),
            ]);
        }

        if (!in_array($type, Question::TYPES, true)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_invalid_type', ['row' => $rowNumber]),
            ]);
        }

        if (!in_array($difficulty, Question::DIFFICULTIES, true)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_invalid_difficulty', ['row' => $rowNumber]),
            ]);
        }

        $status = trim((string) ($row['status'] ?? '')) ?: $defaultStatus;
        if (!in_array($status, ['draft', 'reviewing'], true)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_invalid_status', ['row' => $rowNumber]),
            ]);
        }

        $options = $this->splitImportValue($row['options'] ?? []);
        $correctAnswers = $this->splitImportValue($row['correct_answers'] ?? $row['answer'] ?? []);

        if ($type !== 'short_answer' && count($options) < 2) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_missing_options', ['row' => $rowNumber]),
            ]);
        }

        if (empty($correctAnswers)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_missing_answer', ['row' => $rowNumber]),
            ]);
        }

        return [
            'created_by' => $user->getAuthIdentifier(),
            'folder_id' => $this->folderIdFromImportRow($row, $user, $defaultFolderId),
            'subject' => $subject,
            'topic' => trim((string) ($row['topic'] ?? '')) ?: null,
            'type' => $type,
            'difficulty' => $difficulty,
            'status' => $status,
            'content' => $content,
            'options' => $type === 'short_answer' ? [] : $options,
            'correct_answers' => $correctAnswers,
            'explanation' => trim((string) ($row['explanation'] ?? '')) ?: null,
            'tags' => $this->splitImportValue($row['tags'] ?? []),
        ];
    }

    private function folderIdFromImportRow(array $row, User $user, ?int $defaultFolderId): ?int
    {
        $folderName = trim((string) ($row['folder'] ?? ''));

        if ($folderName === '') {
            return $defaultFolderId;
        }

        $query = QuestionFolder::query()->where('name', $folderName);
        if (!$user->isAdmin()) {
            $query->where('created_by', $user->getAuthIdentifier());
        }

        $folder = $query->first();

        if (!$folder) {
            $folder = QuestionFolder::query()->create([
                'created_by' => $user->getAuthIdentifier(),
                'name' => $folderName,
                'subject' => trim((string) ($row['subject'] ?? '')) ?: null,
                'color' => 'green',
            ]);

            $this->auditFolder('create', [], $folder->only(['id', 'name', 'subject', 'color']), $folder);
        }

        return $folder->id;
    }

    private function splitImportValue(array|string|null $value): array
    {
        if (is_array($value)) {
            return $this->cleanArray($value);
        }

        return collect(preg_split('/\||\R/', (string) $value) ?: [])
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function answersFor(string $type, array $validated, array $options): array
    {
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

    private function authorizeQuestion(Request $request, Question $question, string $permission): void
    {
        $this->authorizePermission($request->user(), $permission);

        if ($request->user()->isAdmin()) {
            return;
        }

        if ((int) $question->created_by !== (int) $request->user()->getAuthIdentifier()) {
            abort(Response::HTTP_FORBIDDEN);
        }
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
        return ['id', 'folder_id', 'subject', 'topic', 'type', 'difficulty', 'status', 'content', 'options', 'correct_answers', 'tags'];
    }

    private function audit(string $action, array $oldValues, array $newValues, Question $question): void
    {
        if (!class_exists(\Mindigo\AuditLog\Services\AuditLogService::class)) {
            return;
        }

        app(\Mindigo\AuditLog\Services\AuditLogService::class)->record(
            $action,
            'questions',
            $oldValues,
            $newValues,
            ['question_id' => $question->id, 'subject' => $question->subject],
            $question
        );
    }

    private function auditFolder(string $action, array $oldValues, array $newValues, QuestionFolder $folder): void
    {
        if (!class_exists(\Mindigo\AuditLog\Services\AuditLogService::class)) {
            return;
        }

        app(\Mindigo\AuditLog\Services\AuditLogService::class)->record(
            $action,
            'question_folders',
            $oldValues,
            $newValues,
            ['folder_id' => $folder->id, 'name' => $folder->name],
            $folder
        );
    }
}
