<?php

namespace Mindigo\TeacherOnboarding\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Mindigo\TeacherOnboarding\Http\Requests\TeacherApplicationProvisioningRequest;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Mindigo\TeacherOnboarding\Services\TeacherApplicationProvisioningService;

class TeacherApplicationProvisioningController extends Controller
{
    public function __construct(private readonly TeacherApplicationProvisioningService $provisioning) {}

    public function update(TeacherApplicationProvisioningRequest $request, TeacherApplication $teacherApplication): RedirectResponse
    {
        $data = $request->validated();

        match ($data['action']) {
            'approve' => $this->provisioning->approve($teacherApplication, $request->user(), $data['note'] ?? null),
            'suspend' => $this->provisioning->suspend($teacherApplication, $request->user(), $data['note']),
            'revoke' => $this->provisioning->revoke($teacherApplication, $request->user(), $data['note']),
        };

        return to_route('admin.teacher-applications.show', $teacherApplication)
            ->with('success', __('teacher-onboarding::provisioning.completed'));
    }
}
