<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\TeacherProfileRequest;
use Mindigo\TeacherCourse\Models\TeacherProfile;
use Mindigo\TeacherCourse\Services\TeacherProfileService;

class TeacherProfileController extends Controller
{
    public function __construct(private readonly TeacherProfileService $profiles) {}

    public function index(Request $request): View
    {
        return view('teacher-course::teachers.index', $this->profiles->directory($request->only(['search', 'specialization'])));
    }

    public function show(int $teacher): View
    {
        return view('teacher-course::teachers.show', $this->profiles->publicProfile($teacher));
    }

    public function edit(Request $request): View
    {
        return view('teacher-course::teachers.edit', ['profile' => $this->profiles->editable($request->user())]);
    }

    public function update(TeacherProfileRequest $request, TeacherProfile $profile): RedirectResponse
    {
        $this->profiles->update($profile, $request->validated());

        return back()->with('success', __('teacher-course::reviews.profile_saved'));
    }
}
