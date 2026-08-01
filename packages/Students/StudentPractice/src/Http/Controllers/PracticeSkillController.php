<?php

namespace Mindigo\StudentPractice\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Mindigo\StudentPractice\Http\Requests\PracticeSkillFilterRequest;
use Mindigo\StudentPractice\Http\Requests\PracticeSkillRequest;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\StudentPractice\Services\PracticeSkillService;

class PracticeSkillController extends Controller
{
    public function __construct(private readonly PracticeSkillService $skills) {}

    public function index(PracticeSkillFilterRequest $request): View
    {
        Gate::forUser($request->user())->authorize('viewAny', PracticeSkill::class);

        return view('student-practice::skills.index', [
            'skills' => $this->skills->filteredList($request->validated()),
            ...$this->skills->formData(),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::forUser($request->user())->authorize('create', PracticeSkill::class);

        return view('student-practice::skills.form', $this->skills->formData());
    }

    public function store(PracticeSkillRequest $request): RedirectResponse
    {
        $this->skills->create($request->user(), $request->validated());

        return to_route('practice.skills.index')->with('success', __('student-practice::app.skills.created'));
    }

    public function edit(Request $request, PracticeSkill $skill): View
    {
        Gate::forUser($request->user())->authorize('update', $skill);

        return view('student-practice::skills.form', $this->skills->formData($skill));
    }

    public function update(PracticeSkillRequest $request, PracticeSkill $skill): RedirectResponse
    {
        $this->skills->update($skill, $request->user(), $request->validated());

        return to_route('practice.skills.index')->with('success', __('student-practice::app.skills.updated'));
    }

    public function destroy(Request $request, PracticeSkill $skill): RedirectResponse
    {
        Gate::forUser($request->user())->authorize('delete', $skill);
        $this->skills->delete($skill);

        return to_route('practice.skills.index')->with('success', __('student-practice::app.skills.deleted'));
    }
}
