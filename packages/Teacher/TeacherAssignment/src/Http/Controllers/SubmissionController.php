<?php

namespace Mindigo\TeacherAssignment\Http\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherAssignment\Http\Requests\AssignmentSubmissionRequest;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherAssignment\Models\AssignmentSubmission;
use Mindigo\TeacherAssignment\Services\AssignmentService;

class SubmissionController extends Controller
{
    public function __construct(protected AssignmentService $service) {}

    public function index(Assignment $assignment)
    {
        abort_if($assignment->teacher_id !== Auth::id(), 403);

        $assignment->load('classroom:id,name,code');

        $studentList = $this->service->getStudentSubmissionList($assignment);
        $stats = $this->service->getStats($assignment);

        return view('teacher-assignment::submissions', compact('assignment', 'studentList', 'stats'));
    }

    public function file(Assignment $assignment, AssignmentSubmission $submission)
    {
        abort_if($assignment->teacher_id !== Auth::id(), 403);
        abort_if($submission->assignment_id !== $assignment->id, 404);
        abort_if(! $submission->hasFile(), 404);

        $path = $submission->file_path;
        abort_if(! Storage::disk('public')->exists($path), 404);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->response($path, $submission->file_original_name ?: basename($path));
    }

    public function grade(AssignmentSubmissionRequest $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        abort_if($assignment->teacher_id !== Auth::id(), 403);
        abort_if($submission->assignment_id !== $assignment->id, 404);

        $this->service->grade($submission, $request->validated());

        return redirect()
            ->route('teacher.assignments.submissions.index', $assignment)
            ->with('success', __('teacher-assignment::app.submission.grade_success', ['name' => $submission->student->name]));
    }

    public function returnAll(Assignment $assignment)
    {
        abort_if($assignment->teacher_id !== Auth::id(), 403);

        $count = $assignment->submissions()
            ->where('status', 'graded')
            ->update(['status' => 'returned']);

        return redirect()
            ->route('teacher.assignments.submissions.index', $assignment)
            ->with('success', __('teacher-assignment::app.submission.return_success', ['count' => $count]));
    }
}
