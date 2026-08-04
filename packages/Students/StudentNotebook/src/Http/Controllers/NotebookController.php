<?php

namespace Mindigo\StudentNotebook\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\StudentNotebook\Http\Requests\NoteRequest;
use Mindigo\StudentNotebook\Models\Note;
use Mindigo\StudentNotebook\Services\NotebookService;

class NotebookController extends Controller
{
    public function __construct(protected NotebookService $service) {}

    public function index(Request $request)
    {
        $studentId = Auth::id();

        $notes = $this->service->listForStudent($studentId);

        $noteParam = $request->input('note');
        $selected = null;
        $creating = false;

        if ($noteParam === 'new') {
            $creating = true;
        } elseif (is_numeric($noteParam)) {
            $selected = $this->service->findForStudent((int) $noteParam, $studentId);
            abort_if($selected === null, 404);
        } elseif ($notes->isNotEmpty()) {
            $selected = $notes->first(); // mặc định chọn ghi chú mới nhất
        } else {
            $creating = true; // chưa có ghi chú nào → mở trình soạn thảo trống
        }

        return view('student-notebook::index', compact('notes', 'selected', 'creating'));
    }

    public function store(NoteRequest $request)
    {
        $note = $this->service->create(Auth::id(), $request->validated());

        return redirect()
            ->route('student.notebook.index', ['note' => $note->id])
            ->with('success', __('student-notebook::app.created_success'));
    }

    public function update(NoteRequest $request, Note $note)
    {
        $this->authorizeOwner($note);
        $this->service->update($note, $request->validated());

        return redirect()
            ->route('student.notebook.index', ['note' => $note->id])
            ->with('success', __('student-notebook::app.updated_success'));
    }

    public function destroy(Note $note)
    {
        $this->authorizeOwner($note);
        $this->service->delete($note);

        return redirect()
            ->route('student.notebook.index')
            ->with('success', __('student-notebook::app.deleted_success'));
    }

    private function authorizeOwner(Note $note): void
    {
        abort_if($note->student_id !== Auth::id(), 403);
    }
}