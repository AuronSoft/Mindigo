<?php

namespace Mindigo\StudentPractice\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Mindigo\StudentPractice\Http\Requests\SkillCatalogRequest;
use Mindigo\StudentPractice\Http\Requests\StartSkillPracticeRequest;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\StudentPractice\Services\PracticeService;
use Mindigo\StudentPractice\Services\SkillProgressService;

class SkillPracticeController extends Controller
{
    public function __construct(
        private readonly PracticeService $practice,
        private readonly SkillProgressService $progress,
    ) {}

    public function index(SkillCatalogRequest $request): View
    {
        return view('student-practice::skill-practice.index', $this->progress->catalog($request->user(), $request->validated()));
    }

    public function show(SkillCatalogRequest $request, PracticeSkill $skill): View
    {
        Gate::forUser($request->user())->authorize('view', $skill);

        return view('student-practice::skill-practice.show', $this->progress->details($request->user(), $skill));
    }

    public function start(StartSkillPracticeRequest $request, PracticeSkill $skill): RedirectResponse
    {
        $attempt = $this->practice->startSkillPractice($request->user(), $skill, $request->validated());

        return to_route('student.practice.attempt', $attempt);
    }
}
