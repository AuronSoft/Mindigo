<?php

namespace Mindigo\QuestionBank\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Http\Requests\QuestionFolderRequest;
use Mindigo\QuestionBank\Http\Requests\QuestionImportRequest;
use Mindigo\QuestionBank\Http\Requests\QuestionRequest;
use Mindigo\QuestionBank\Http\Requests\QuestionReviewRequest;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\QuestionBank\Services\QuestionBankService;
use Symfony\Component\HttpFoundation\Response;

class QuestionBankController extends Controller
{
    public function __construct(private QuestionBankService $questions)
    {
    }

    public function index(Request $request)
    {
        $this->authorizePermission($request->user(), 'questions.view');

        return view('Mindigo-question-bank::index', [
            'questions' => $this->questions->filteredList($request->user(), $request->only(['keyword', 'subject', 'type', 'difficulty', 'status', 'folder_id'])),
            'stats' => $this->questions->stats($request->user()),
            'types' => Question::TYPES,
            'difficulties' => Question::DIFFICULTIES,
            'statuses' => Question::STATUSES,
            'subjects' => $this->questions->subjects(),
            'folders' => $this->questions->foldersFor($request->user()),
            'currentFolderId' => $request->input('folder_id'),
            'filters' => $request->only(['keyword', 'subject', 'type', 'difficulty', 'status', 'folder_id']),
        ]);
    }

    public function storeFolder(QuestionFolderRequest $request): RedirectResponse
    {
        $folder = $this->questions->createFolder($request->user(), $request->validated());

        return redirect()
            ->route('question-bank.index', ['folder_id' => $folder->id])
            ->with('success', __('Mindigo-question-bank::app.messages.folder_created'));
    }

    public function import(QuestionImportRequest $request): RedirectResponse
    {
        $created = $this->questions->import(
            $request->file('import_file'),
            $request->user(),
            $request->validated('status'),
            $request->validated('folder_id')
        );

        return redirect()
            ->route('question-bank.index', $request->validated('folder_id') ? ['folder_id' => $request->validated('folder_id')] : [])
            ->with('success', trans_choice('Mindigo-question-bank::app.messages.imported', $created, ['count' => $created]));
    }

    public function create(Request $request)
    {
        $this->authorizePermission($request->user(), 'questions.create');

        return view('Mindigo-question-bank::create', $this->questions->formData($request->user()));
    }

    public function store(QuestionRequest $request): RedirectResponse
    {
        $question = $this->questions->createQuestion($request->user(), $request->questionData());

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

        return view('Mindigo-question-bank::edit', array_merge($this->questions->formData($request->user()), [
            'question' => $question,
        ]));
    }

    public function update(QuestionRequest $request, Question $question): RedirectResponse
    {
        $this->authorizeQuestion($request, $question, 'questions.update');
        $this->questions->updateQuestion($question, $request->questionData($question));

        return redirect()
            ->route('question-bank.show', $question)
            ->with('success', __('Mindigo-question-bank::app.messages.updated'));
    }

    public function review(QuestionReviewRequest $request, Question $question): RedirectResponse
    {
        $this->questions->review($question, $request->user(), $request->validated());

        return back()->with('success', __('Mindigo-question-bank::app.messages.reviewed'));
    }

    public function destroy(Request $request, Question $question): RedirectResponse
    {
        $this->authorizeQuestion($request, $question, 'questions.delete');
        $this->questions->delete($question);

        return redirect()
            ->route('question-bank.index')
            ->with('success', __('Mindigo-question-bank::app.messages.deleted'));
    }

    private function authorizeQuestion(Request $request, Question $question, string $permission): void
    {
        $this->authorizePermission($request->user(), $permission);

        if ($this->questions->canAccessQuestion($request->user(), $question)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }

    private function authorizePermission(User $user, string $permission): void
    {
        if ($this->questions->can($user, $permission)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}
