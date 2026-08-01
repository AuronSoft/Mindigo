<?php

namespace Mindigo\TeacherExam\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\TeacherExam\Http\Requests\GradeExamAttemptRequest;
use Mindigo\TeacherExam\Http\Requests\MonitorExamRequest;
use Mindigo\TeacherExam\Http\Requests\TeacherExamRequest;
use Mindigo\TeacherExam\Services\TeacherExamService;

class TeacherExamController extends Controller
{
    public function __construct(private readonly TeacherExamService $service) {}

    public function index()
    {
        session()->forget('url.intended');

        /** @var User $teacher */
        $teacher = Auth::user();
        $filters = request()->only(['keyword', 'status']);

        return view('teacher-exam::index', [
            'exams' => $this->service->ownedList($teacher, $filters),
            'stats' => $this->service->stats($teacher),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('teacher-exam::create', $this->service->formData(Auth::user()));
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
            ...$this->service->formData(Auth::user()),
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

    public function grade(Exam $exam, ExamAttempt $attempt)
    {
        $this->authorizeOwnership($exam);
        abort_unless((int) $attempt->exam_id === (int) $exam->id, 404);
        abort_unless(in_array($attempt->status, ['submitted', 'expired'], true), 422);

        return view('teacher-exam::grade', $this->service->gradingData($attempt));
    }

    public function updateGrade(GradeExamAttemptRequest $request, Exam $exam, ExamAttempt $attempt): RedirectResponse
    {
        $this->authorizeOwnership($exam);
        abort_unless((int) $attempt->exam_id === (int) $exam->id, 404);
        abort_unless(in_array($attempt->status, ['submitted', 'expired'], true), 422);

        $this->service->gradeAttempt($attempt, $request->validated('grades'), Auth::user());

        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', __('teacher-exam::app.graded_successfully'));
    }

    public function monitor(MonitorExamRequest $request, Exam $exam)
    {
        $this->authorizeOwnership($exam);

        return view('teacher-exam::monitor', [
            'exam' => $exam,
            ...$this->service->monitoringData($exam, Auth::user(), $request->validated()),
        ]);
    }

    public function monitorData(MonitorExamRequest $request, Exam $exam): JsonResponse
    {
        $this->authorizeOwnership($exam);
        $data = $this->service->monitoringData($exam, Auth::user(), $request->validated());

        return response()->json([
            'summary' => $data['summary'],
            'students' => $data['students']->items(),
            'refreshed_at' => now()->toIso8601String(),
        ]);
    }

    private function authorizeOwnership(Exam $exam): void
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless(
            $user->isAdmin() || $exam->created_by === (int) $user->getAuthIdentifier(),
            403,
            'Bạn không có quyền truy cập đề thi này.'
        );
    }

    // in pdf
    public function print(Exam $exam): Response
    {
        $this->authorizeOwnership($exam);

        $exam->load('questions');

        $pdf = app('dompdf.wrapper')
            ->loadView('teacher-exam::print', compact('exam'))
            ->setPaper('a4', 'portrait');

        return $pdf->download(Str::slug($exam->title).'.pdf');
    }
}
