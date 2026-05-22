<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Http\Requests\ExamRequest;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Services\ExamService;
use Symfony\Component\HttpFoundation\Response;

class ExamController extends Controller
{
    public function __construct(private ExamService $exams)
    {
    }

    public function index(Request $request)
    {
        $this->authorizePermission($request->user(), 'exams.view');

        return view('Mindigo-exam-management::index', [
            'exams' => $this->exams->filteredList($request->only(['keyword', 'status', 'subject'])),
            'stats' => $this->exams->stats(),
            'statuses' => Exam::STATUSES,
            'subjects' => $this->exams->subjects(),
            'filters' => $request->only(['keyword', 'status', 'subject']),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizePermission($request->user(), 'exams.create');

        return view('Mindigo-exam-management::create', $this->exams->formData());
    }

    public function store(ExamRequest $request): RedirectResponse
    {
        $exam = $this->exams->create($request);

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

        return view('Mindigo-exam-management::edit', array_merge($this->exams->formData(), [
            'exam' => $exam,
        ]));
    }

    public function update(ExamRequest $request, Exam $exam): RedirectResponse
    {
        $this->exams->update($exam, $request);

        return redirect()
            ->route('exams.show', $exam)
            ->with('success', __('Mindigo-exam-management::app.messages.updated'));
    }

    public function publish(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'exams.publish');

        try {
            $this->exams->publish($exam);
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return back()->with('success', __('Mindigo-exam-management::app.messages.published'));
    }

    public function close(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'exams.publish');
        $this->exams->close($exam);

        return back()->with('success', __('Mindigo-exam-management::app.messages.closed'));
    }

    public function destroy(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'exams.delete');
        $this->exams->delete($exam);

        return redirect()
            ->route('exams.index')
            ->with('success', __('Mindigo-exam-management::app.messages.deleted'));
    }

    private function authorizePermission(User $user, string $permission): void
    {
        if ($this->exams->can($user, $permission)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}
