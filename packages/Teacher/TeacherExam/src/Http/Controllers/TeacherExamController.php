<?php

namespace Mindigo\TeacherExam\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\TeacherExam\Http\Requests\TeacherExamRequest;
use Mindigo\TeacherExam\Services\TeacherExamService;

class TeacherExamController extends Controller
{
    public function __construct(private readonly TeacherExamService $service) {}

    public function index()
    {
        session()->forget('url.intended');

        /** @var \Mindigo\Auth\Models\User $teacher */
        $teacher = Auth::user();
        $filters = request()->only(['keyword', 'status']);

        return view('teacher-exam::index', [
            'exams'   => $this->service->ownedList($teacher, $filters),
            'stats'   => $this->service->stats($teacher),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('teacher-exam::create', $this->service->formData());
    }

    public function store(TeacherExamRequest $request): RedirectResponse
    {
        $exam = $this->service->create($request);

        return redirect()
            ->route('teacher.exams.show', $exam)
            ->with('success', __('teacher-exam::app.created'));
    }

    public function show(Exam $exam)
    {
        $this->authorizeOwnership($exam);

        $exam->load('questions');
        $results = $this->service->examResults($exam);

        return view('teacher-exam::show', compact('exam', 'results'));
    }

    public function edit(Exam $exam)
    {
        $this->authorizeOwnership($exam);

        return view('teacher-exam::edit', [
            'exam' => $exam,
            ...$this->service->formData(),
        ]);
    }

    public function update(TeacherExamRequest $request, Exam $exam): RedirectResponse
    {
        $this->authorizeOwnership($exam);

        $this->service->update($exam, $request);

        return redirect()
            ->route('teacher.exams.show', $exam)
            ->with('success', __('teacher-exam::app.updated'));
    }

    public function publish(Exam $exam): RedirectResponse
    {
        $this->authorizeOwnership($exam);

        $this->service->publish($exam);

        return redirect()
            ->route('teacher.exams.show', $exam)
            ->with('success', __('teacher-exam::app.msg_published'));
    }

    public function close(Exam $exam): RedirectResponse
    {
        $this->authorizeOwnership($exam);

        $this->service->close($exam);

        return redirect()
            ->route('teacher.exams.show', $exam)
            ->with('success', __('teacher-exam::app.msg_closed'));
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->authorizeOwnership($exam);

        $this->service->delete($exam);

        return redirect()
            ->route('teacher.exams.index')
            ->with('success', __('teacher-exam::app.deleted'));
    }

    private function authorizeOwnership(Exam $exam): void
    {
        /** @var \Mindigo\Auth\Models\User $user */
        $user = Auth::user();

        abort_unless(
            $user->isAdmin() || $exam->created_by === (int) $user->getAuthIdentifier(),
            403,
            'Bạn không có quyền truy cập đề thi này.'
        );
    }
}
