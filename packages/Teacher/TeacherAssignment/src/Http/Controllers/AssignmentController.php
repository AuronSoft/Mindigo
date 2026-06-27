<?php

namespace Mindigo\TeacherAssignment\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherAssignment\Http\Requests\AssignmentRequest;
use Mindigo\TeacherAssignment\Http\Requests\AssignmentSubmissionRequest;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherAssignment\Services\AssignmentService;

class AssignmentController extends Controller
{
    public function __construct(protected AssignmentService $service)
    {
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $searchTitle = $request->input('search_title');
        $classroomId = $request->input('classroom_id');

        $query = \Mindigo\TeacherAssignment\Models\Assignment::query()
            ->where('teacher_id', auth()->id())
            ->with('classroom');

        if (!empty($searchTitle)) {
            $query->where('title', 'like', '%' . $searchTitle . '%');
        }

        if (!empty($classroomId)) {
            $query->where('classroom_id', $classroomId);
        }

        $assignments = $query->latest()->paginate(10)->withQueryString();

        $classrooms = $this->classroomsForTeacher();
        return view('teacher-assignment::index', compact('assignments', 'classrooms'));
    }

    public function create()
    {
        $classrooms = $this->classroomsForTeacher();

        return view('teacher-assignment::create', compact('classrooms'));
    }

    public function store(AssignmentRequest $request)
    {
        $data = $request->except('files');
        $this->service->create($data, $request->file('files'));

        return redirect()
            ->route('teacher.assignments.index')
            ->with('success', __('teacher-assignment::app.assignment.created_success'));
    }

    public function file(Assignment $assignment, int $fileIndex)
    {
        $this->authorize($assignment);

        $files = is_array($assignment->file_path) ? array_values($assignment->file_path) : [];
        $path = $files[$fileIndex] ?? null;

        abort_if(! $path || ! Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path, basename($path));
    }

    public function edit(Assignment $assignment)
    {
        $this->authorize($assignment);

        $classrooms = $this->classroomsForTeacher();

        return view('teacher-assignment::edit', compact('assignment', 'classrooms'));
    }

    public function update(AssignmentRequest $request, Assignment $assignment)
    {
        $this->authorize($assignment);
        $this->service->update($assignment, $request->except('files'), $request->file('files'));

        return redirect()
            ->route('teacher.assignments.index')
            ->with('success', __('teacher-assignment::app.assignment.updated_success'));
    }

    public function destroy(Assignment $assignment)
    {
        $this->authorize($assignment);
        $this->service->delete($assignment);

        return redirect()
            ->route('teacher.assignments.index')
            ->with('success', __('teacher-assignment::app.assignment.deleted_success'));
    }

    private function authorize(Assignment $assignment): void
    {
        abort_if($assignment->teacher_id !== auth()->id(), 403);
    }

    private function classroomsForTeacher()
    {
        return \Mindigo\ClassroomManagement\Models\Classroom::query()
            ->where('teacher_id', auth()->id())
            ->where('status', 'active')
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }
}
