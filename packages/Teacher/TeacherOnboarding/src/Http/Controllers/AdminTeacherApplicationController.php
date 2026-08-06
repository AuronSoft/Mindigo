<?php

namespace Mindigo\TeacherOnboarding\Http\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Mindigo\TeacherOnboarding\Http\Requests\AdminTeacherApplicationActionRequest;
use Mindigo\TeacherOnboarding\Http\Requests\AdminTeacherApplicationIndexRequest;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Mindigo\TeacherOnboarding\Services\TeacherApplicationService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminTeacherApplicationController extends Controller
{
    public function __construct(private readonly TeacherApplicationService $applications) {}

    public function index(AdminTeacherApplicationIndexRequest $request): View
    {
        $filters = $request->safe()->only(['search', 'status', 'application_type', 'sort']);

        return view('teacher-onboarding::admin.applications.index', [
            'applications' => $this->applications->queue($filters),
            'filters' => $filters,
            'statuses' => $this->applications->reviewableStatuses(),
            'applicationTypes' => TeacherApplication::APPLICATION_TYPES,
            'summary' => $this->applications->summary(),
            'applicants' => $this->applications->applicants(),
        ]);
    }

    public function show(TeacherApplication $teacherApplication): View
    {
        Gate::authorize('view', $teacherApplication);

        return view('teacher-onboarding::admin.applications.show', [
            'application' => $this->applications->detail($teacherApplication),
            'nextStatuses' => $this->applications->nextStatuses($teacherApplication),
        ]);
    }

    public function update(AdminTeacherApplicationActionRequest $request, TeacherApplication $teacherApplication): RedirectResponse
    {
        $this->applications->transition($teacherApplication, $request->user(), $request->validated());

        return to_route('admin.teacher-applications.show', $teacherApplication)
            ->with('success', __('teacher-onboarding::admin.action_completed'));
    }

    public function document(TeacherApplication $teacherApplication, string $document): StreamedResponse
    {
        Gate::authorize('viewDocument', $teacherApplication);

        $metadata = $this->applications->document($teacherApplication, $document);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($metadata['disk']);

        return $disk->download($metadata['path'], $metadata['name']);
    }
}
