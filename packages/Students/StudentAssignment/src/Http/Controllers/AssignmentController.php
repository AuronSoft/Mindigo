<?php

namespace Mindigo\StudentAssignment\Http\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\StudentAssignment\Http\Requests\SubmitAssignmentRequest;
use Mindigo\StudentAssignment\Services\AssignmentService;
use Mindigo\TeacherAssignment\Models\Assignment;

class AssignmentController extends Controller
{
    public function __construct(protected AssignmentService $service) {}

    public function index(Request $request)
    {
        $studentId = (int) Auth::id();
        $filters = $request->only(['status', 'classroom_id']);
        $assignments = $this->service->getAssignmentsForStudent($studentId, $filters);
        $classrooms = Classroom::query()
            ->whereHas('studentLinks', fn ($query) => $query->where('student_id', $studentId)->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('student-assignment::index', compact('assignments', 'classrooms', 'filters'));
    }

    public function show(Assignment $assignment)
    {
        $studentId = (int) Auth::id();
        $this->service->assertStudentCanAccess($assignment, $studentId);
        $assignment->load('classroom:id,name,code');
        $submission = $this->service->findSubmission($assignment, $studentId);
        $canSubmit = $this->service->canSubmit($assignment, $submission);

        return view('student-assignment::show', compact('assignment', 'submission', 'canSubmit'));
    }

    public function submit(SubmitAssignmentRequest $request, Assignment $assignment)
    {
        $studentId = (int) Auth::id();
        $this->service->assertStudentCanAccess($assignment, $studentId);
        $this->service->submit($assignment, $studentId, $request->validated(), $request->file('submission_file'));

        return redirect()->route('student.assignments.show', $assignment)
            ->with('success', __('student-assignment::app.messages.submitted'));
    }

    public function assignmentFile(Assignment $assignment, int $fileIndex)
    {
        $this->service->assertStudentCanAccess($assignment, (int) Auth::id());

        $files = is_array($assignment->file_path) ? array_values($assignment->file_path) : [];
        $path = $files[$fileIndex] ?? null;
        abort_if(! $path || ! Storage::disk('public')->exists($path), 404);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->response($path, basename($path));
    }

    public function submissionFile(Assignment $assignment)
    {
        $studentId = (int) Auth::id();
        $this->service->assertStudentCanAccess($assignment, $studentId);
        $submission = $this->service->findSubmission($assignment, $studentId);
        abort_if(! $submission?->hasFile() || ! Storage::disk('public')->exists($submission->file_path), 404);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->response(
            $submission->file_path,
            $submission->file_original_name ?: basename($submission->file_path)
        );
    }
}
