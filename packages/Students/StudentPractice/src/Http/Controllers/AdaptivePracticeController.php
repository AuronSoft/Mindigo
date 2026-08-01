<?php

namespace Mindigo\StudentPractice\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\StudentPractice\Http\Requests\StartAdaptivePracticeRequest;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\StudentPractice\Models\StudentSkillProgress;
use Mindigo\StudentPractice\Services\PracticeRecommendationService;
use Mindigo\StudentPractice\Services\PracticeService;

class AdaptivePracticeController extends Controller
{
    public function __construct(
        private readonly PracticeService $practice,
        private readonly PracticeRecommendationService $recommendations,
    ) {}

    public function index(Request $request): View
    {
        return view('student-practice::adaptive.index', [
            'recommendations' => $this->recommendations->feed($request->user()),
            'progress' => StudentSkillProgress::query()->with(['skill.subject:id,name'])
                ->where('student_id', $request->user()->getAuthIdentifier())
                ->orderBy('mastery_score')->paginate(12),
        ]);
    }

    public function start(StartAdaptivePracticeRequest $request, PracticeSkill $skill): RedirectResponse
    {
        $attempt = $this->practice->startAdaptivePractice(
            $request->user(),
            $skill,
            $request->integer('question_count'),
        );

        return to_route('student.practice.attempt', $attempt);
    }
}
