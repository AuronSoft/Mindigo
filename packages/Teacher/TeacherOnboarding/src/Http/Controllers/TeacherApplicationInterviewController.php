<?php

namespace Mindigo\TeacherOnboarding\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Mindigo\TeacherOnboarding\Http\Requests\TeacherApplicationInterviewEvaluationRequest;
use Mindigo\TeacherOnboarding\Http\Requests\TeacherApplicationInterviewRequest;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Mindigo\TeacherOnboarding\Models\TeacherApplicationInterview;
use Mindigo\TeacherOnboarding\Services\TeacherApplicationInterviewService;

class TeacherApplicationInterviewController extends Controller
{
    public function __construct(private readonly TeacherApplicationInterviewService $interviews) {}

    public function store(TeacherApplicationInterviewRequest $request, TeacherApplication $teacherApplication): RedirectResponse
    {
        $interview = $this->interviews->create($teacherApplication, $request->user(), $request->validated());

        return to_route('admin.teacher-applications.interviews.show', [$teacherApplication, $interview])
            ->with('success', __('teacher-onboarding::interview.scheduled'));
    }

    public function show(TeacherApplication $teacherApplication, TeacherApplicationInterview $interview): View
    {
        abort_unless((int) $interview->teacher_application_id === (int) $teacherApplication->id, 404);
        Gate::authorize('view', $teacherApplication);

        return view('teacher-onboarding::admin.interviews.show', [
            'application' => $teacherApplication->load(['subject:id,name', 'category:id,name']),
            'interview' => $this->interviews->detail($interview),
            'modes' => TeacherApplicationInterview::MODES,
            'results' => TeacherApplicationInterview::RESULTS,
        ]);
    }

    public function update(TeacherApplicationInterviewRequest $request, TeacherApplication $teacherApplication, TeacherApplicationInterview $interview): RedirectResponse
    {
        abort_unless((int) $interview->teacher_application_id === (int) $teacherApplication->id, 404);

        $this->interviews->updateSchedule($interview, $request->user(), $request->validated());

        return to_route('admin.teacher-applications.interviews.show', [$teacherApplication, $interview])
            ->with('success', __('teacher-onboarding::interview.rescheduled'));
    }

    public function evaluate(TeacherApplicationInterviewEvaluationRequest $request, TeacherApplication $teacherApplication, TeacherApplicationInterview $interview): RedirectResponse
    {
        abort_unless((int) $interview->teacher_application_id === (int) $teacherApplication->id, 404);

        $this->interviews->evaluate($interview, $request->user(), $request->validated());

        return to_route('admin.teacher-applications.interviews.show', [$teacherApplication, $interview])
            ->with('success', __('teacher-onboarding::interview.evaluated'));
    }
}
