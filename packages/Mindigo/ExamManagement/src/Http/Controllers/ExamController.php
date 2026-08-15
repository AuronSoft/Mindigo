<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Http\Requests\ExamRequest;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Services\ExamCutoverService;
use Mindigo\ExamManagement\Services\ExamService;
use Symfony\Component\HttpFoundation\Response;

class ExamController extends Controller
{
    public function __construct(private ExamService $exams, private ExamCutoverService $cutover) {}

    public function index(Request $request)
    {
        if ($this->cutover->prefersNew($request->user())) {
            return $request->user()->isAdmin()
                ? redirect()->route('admin.exam-operations')
                : redirect()->route('teacher.exam-templates.index');
        }
        $this->authorizePermission($request->user(), 'exams.view');

        return view('Mindigo-exam-management::index', [
            'exams' => $this->exams->filteredList($request->only(['keyword', 'status', 'subject']), $request->user()),
            'stats' => $this->exams->stats($request->user()),
            'statuses' => Exam::STATUSES,
            'subjects' => $this->exams->subjects(),
            'filters' => $request->only(['keyword', 'status', 'subject']),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeLegacyWrite($request);
        $this->authorizePermission($request->user(), 'exams.create');

        return view('Mindigo-exam-management::create', $this->exams->formData());
    }

    public function store(ExamRequest $request): RedirectResponse
    {
        $this->authorizeLegacyWrite($request);
        $exam = $this->exams->create($request);

        return redirect()
            ->route('exams.show', $exam)
            ->with('success', __('Mindigo-exam-management::app.messages.created'));
    }

    public function show(Request $request, Exam $exam)
    {
        $this->authorizeExam($request->user(), $exam, 'exams.view');
        $exam->load(['creator:id,name,email,role', 'questions', 'attempts.user:id,name,email,role']);

        return view('Mindigo-exam-management::show', [
            'exam' => $exam,
        ]);
    }

    public function edit(Request $request, Exam $exam)
    {
        $this->authorizeLegacyWrite($request);
        $this->authorizeExam($request->user(), $exam, 'exams.update');
        $exam->load('questions');

        return view('Mindigo-exam-management::edit', array_merge($this->exams->formData(), [
            'exam' => $exam,
        ]));
    }

    public function update(ExamRequest $request, Exam $exam): RedirectResponse
    {
        $this->authorizeLegacyWrite($request);
        $this->authorizeExam($request->user(), $exam, 'exams.update');
        $this->exams->update($exam, $request);

        return redirect()
            ->route('exams.show', $exam)
            ->with('success', __('Mindigo-exam-management::app.messages.updated'));
    }

    public function publish(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeLegacyWrite($request);
        $this->authorizeExam($request->user(), $exam, 'exams.publish');

        try {
            $this->exams->publish($exam);
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return back()->with('success', __('Mindigo-exam-management::app.messages.published'));
    }

    public function close(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeLegacyWrite($request);
        $this->authorizeExam($request->user(), $exam, 'exams.publish');
        $this->exams->close($exam);

        return back()->with('success', __('Mindigo-exam-management::app.messages.closed'));
    }

    public function destroy(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeLegacyWrite($request);
        $this->authorizeExam($request->user(), $exam, 'exams.delete');
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

    private function authorizeLegacyWrite(Request $request): void
    {
        abort_unless($this->cutover->legacyWritable($request->user()), Response::HTTP_LOCKED, 'The legacy exam module is read-only after cutover.');
    }

    private function authorizeExam(User $user, Exam $exam, string $permission): void
    {
        $this->authorizePermission($user, $permission);

        abort_unless(
            $user->isAdmin() || (int) $exam->created_by === (int) $user->getAuthIdentifier(),
            Response::HTTP_FORBIDDEN
        );
    }
}
