<?php

namespace Mindigo\TeacherQuestion\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\TeacherQuestion\Http\Requests\TeacherQuestionRequest;
use Mindigo\TeacherQuestion\Services\TeacherQuestionService;

class TeacherQuestionController extends Controller
{
    public function __construct(private readonly TeacherQuestionService $service) {}

    public function index()
    {
        session()->forget('url.intended');

        /** @var \Mindigo\Auth\Models\User $teacher */
        $teacher = Auth::user();
        $filters = request()->only(['keyword', 'subject', 'type', 'difficulty', 'status', 'folder_id']);

        return view('teacher-question::index', [
            'questions' => $this->service->filteredList($teacher, $filters),
            'stats'     => $this->service->stats($teacher),
            'filters'   => $filters,
            ...$this->service->formData($teacher),
        ]);
    }

    public function create()
    {
        /** @var \Mindigo\Auth\Models\User $teacher */
        $teacher = Auth::user();

        return view('teacher-question::create', $this->service->formData($teacher));
    }

    public function store(TeacherQuestionRequest $request): RedirectResponse
    {
        $question = $this->service->create($request);

        return redirect()
            ->route('teacher.questions.show', $question)
            ->with('success', __('teacher-question::app.created'));
    }

    public function show(Question $question)
    {
        $this->authorizeOwnership($question);

         $question->load(['editHistories.editor']); // record

        return view('teacher-question::show', compact('question'));
    }

    public function bulkDifficulty(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'        => ['required', 'string'],
            'difficulty' => ['required', Rule::in(Question::DIFFICULTIES)],
        ]);

        $teacher = Auth::user();
        $ids = array_filter(explode(',', $request->input('ids')));

        $this->service->bulkUpdateDifficulty($teacher, $ids, $request->input('difficulty'));

        return back()->with('success', 'Đã cập nhật độ khó cho ' . count($ids) . ' câu hỏi.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate(['ids' => ['required', 'string']]);

        $teacher = Auth::user();
        $ids = array_filter(explode(',', $request->input('ids')));

        $this->service->bulkDelete($teacher, $ids);

        return back()->with('success', 'Đã xóa ' . count($ids) . ' câu hỏi.');
    }

    public function bulkStatus(Request $request): RedirectResponse
{
    $request->validate([
        'ids'    => ['required', 'string'],
        'status' => ['required', Rule::in(['draft', 'reviewing', 'approved'])],
    ]);

    $teacher = Auth::user();
    $ids = array_filter(explode(',', $request->input('ids')));

    $this->service->bulkUpdateStatus($teacher, $ids, $request->input('status'));

    return back()->with('success', 'Đã cập nhật trạng thái cho ' . count($ids) . ' câu hỏi.');
}

    public function edit(Question $question)
    {
        $this->authorizeOwnership($question);

        /** @var \Mindigo\Auth\Models\User $teacher */
        $teacher = Auth::user();

        return view('teacher-question::edit', [
            'question' => $question,
            ...$this->service->formData($teacher),
        ]);
    }

    public function update(TeacherQuestionRequest $request, Question $question): RedirectResponse
    {
        $this->authorizeOwnership($question);

        $this->service->update($question, $request);

        return redirect()
            ->route('teacher.questions.show', $question)
            ->with('success', __('teacher-question::app.updated'));
    }

    public function submit(Question $question): RedirectResponse
    {
        $this->authorizeOwnership($question);

        $this->service->submitForReview($question);

        return redirect()
            ->route('teacher.questions.show', $question)
            ->with('success', __('teacher-question::app.submitted'));
    }

    public function destroy(Question $question): RedirectResponse
    {
        $this->authorizeOwnership($question);

        $this->service->delete($question);

        return redirect()
            ->route('teacher.questions.index')
            ->with('success', __('teacher-question::app.deleted'));
    }

    public function importForm()
{
    $teacher = Auth::user();

    return view('teacher-question::import', [
        'folders' => $this->service->myFolders($teacher),
        'subjects' => $this->service->formData($teacher)['subjects'],
    ]);
}

   public function importStore(Request $request): RedirectResponse
{
    $teacher = Auth::user();

    // Nếu có docx_parsed → đã parse ở browser bằng mammoth
    if ($request->filled('docx_parsed')) {
        $request->validate([
            'docx_parsed' => ['required', 'string'],
            'folder_id'   => ['nullable', 'exists:question_bank_folders,id'],
            'status'      => ['required', 'in:draft,reviewing'],
        ]);

        try {
            $rows  = json_decode($request->input('docx_parsed'), true);
            $subject = $request->input('docx_subject');
            $rows = array_map(fn($row) => array_merge($row, [
                'subject' => $row['subject'] ?? $subject,
            ]), $rows);

            $count = $this->service->importFromRows(
                $rows,
                $teacher,
                $request->input('status', 'draft'),
                $request->input('folder_id') ? (int) $request->input('folder_id') : null,
            );

            return redirect()
                ->route('teacher.questions.index')
                ->with('success', __('teacher-question::app.imported', ['count' => $count]));
        } catch (\Throwable $e) {
            return back()->withErrors(['import_file' => $e->getMessage()])->withInput();
        }
    }

    // CSV / TXT / JSON → xử lý server-side như cũ
    $request->validate([
        'import_file' => ['required', 'file', 'max:5120', 'extensions:csv,txt,json'],
        'folder_id'   => ['nullable', 'exists:question_bank_folders,id'],
        'status'      => ['required', 'in:draft,reviewing'],
    ]);

    try {
        $count = $this->service->import(
            $request->file('import_file'),
            $teacher,
            $request->input('status', 'draft'),
            $request->input('folder_id') ? (int) $request->input('folder_id') : null,
        );

        return redirect()
            ->route('teacher.questions.index')
            ->with('success', __('teacher-question::app.imported', ['count' => $count]));
    } catch (ValidationException $e) {
        return back()->withErrors($e->errors())->withInput();
    } catch (\Throwable $e) {
        return back()->withErrors(['import_file' => $e->getMessage()])->withInput();
    }
}

    private function authorizeOwnership(Question $question): void
    {
        /** @var \Mindigo\Auth\Models\User $user */
        $user = Auth::user();

        abort_unless(
            $user->isAdmin() || $question->created_by === (int) $user->getAuthIdentifier(),
            403,
            'Bạn không có quyền truy cập câu hỏi này.'
        );
    }
}
