<?php

namespace Mindigo\ClassroomManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Http\Requests\ClassroomRequest;
use Mindigo\ClassroomManagement\Http\Requests\ClassroomRosterRequest;
use Mindigo\ClassroomManagement\Http\Requests\ClassroomSubjectRequest;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ClassroomManagement\Services\ClassroomManagementService;
use Symfony\Component\HttpFoundation\Response;

class ClassroomController extends Controller
{
    public function __construct(private ClassroomManagementService $classrooms)
    {
    }

    public function index(Request $request)
    {
        $this->authorizePermission($request->user(), 'classrooms.view');
        $formData = $this->classrooms->formData($request->user());

        return view('Mindigo-classroom-management::index', [
            'classrooms' => $this->classrooms->filteredList($request->user(), $request->only(['keyword', 'status', 'teacher_id', 'subject_id'])),
            'stats' => $this->classrooms->stats($request->user()),
            'filters' => $request->only(['keyword', 'status', 'teacher_id', 'subject_id']),
            ...$formData,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizePermission($request->user(), 'classrooms.create');

        return view('Mindigo-classroom-management::create', $this->classrooms->formData($request->user()));
    }

    public function store(ClassroomRequest $request): RedirectResponse
    {
        $classroom = $this->classrooms->create($request->user(), $request->classroomData());

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', __('Mindigo-classroom-management::app.messages.created'));
    }

    public function show(Request $request, Classroom $classroom)
    {
        $this->authorizeClassroom($request, $classroom, 'classrooms.view');
        $classroom->load(['teacher:id,name,email', 'students:id,name,email,role', 'subjects:id,name,color']);

        return view('Mindigo-classroom-management::show', [
            'classroom' => $classroom,
            ...$this->classrooms->formData($request->user()),
        ]);
    }

    public function edit(Request $request, Classroom $classroom)
    {
        $this->authorizeClassroom($request, $classroom, 'classrooms.update');

        return view('Mindigo-classroom-management::edit', [
            'classroom' => $classroom,
            ...$this->classrooms->formData($request->user()),
        ]);
    }

    public function update(ClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeClassroom($request, $classroom, 'classrooms.update');
        $this->classrooms->update($classroom, $request->classroomData());

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', __('Mindigo-classroom-management::app.messages.updated'));
    }

    public function destroy(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeClassroom($request, $classroom, 'classrooms.delete');
        $this->classrooms->delete($classroom);

        return redirect()
            ->route('classrooms.index')
            ->with('success', __('Mindigo-classroom-management::app.messages.deleted'));
    }

    public function syncStudents(ClassroomRosterRequest $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeClassroom($request, $classroom, 'classrooms.manage_students');
        $this->classrooms->syncStudents($classroom, $request->studentIds());

        return back()->with('success', __('Mindigo-classroom-management::app.messages.students_synced'));
    }

    public function syncSubjects(ClassroomSubjectRequest $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeClassroom($request, $classroom, 'classrooms.update');
        $this->classrooms->syncSubjects($classroom, $request->subjectIds());

        return back()->with('success', __('Mindigo-classroom-management::app.messages.subjects_synced'));
    }

    private function authorizeClassroom(Request $request, Classroom $classroom, string $permission): void
    {
        $this->authorizePermission($request->user(), $permission);

        if ($this->classrooms->canAccess($request->user(), $classroom)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }

    private function authorizePermission(User $user, string $permission): void
    {
        if ($this->classrooms->can($user, $permission)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}
