<?php

namespace Mindigo\TeacherOnboarding\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Mindigo\TeacherOnboarding\Http\Requests\TeacherApplicationRequest;
use Mindigo\TeacherOnboarding\Services\TeacherApplicationService;

class TeacherApplicationController extends Controller
{
    public function __construct(private readonly TeacherApplicationService $applications) {}

    public function create(): View
    {
        return view('teacher-onboarding::applications.create', [
            ...$this->applications->options(),
            'user' => Auth::user(),
        ]);
    }

    public function store(TeacherApplicationRequest $request): RedirectResponse
    {
        $application = $this->applications->submit(
            $request->user(),
            $request->safe()->except([
                'terms_accepted',
                'identity_document',
                'degree_document',
                'certificate_document',
                'student_card_document',
                'cv_document',
                'portfolio_document',
            ]),
            $request->documentFiles(),
        );

        return to_route('teacher-applications.create')
            ->with('success', __('teacher-onboarding::application.submitted', [
                'code' => $application->application_code,
            ]));
    }
}
