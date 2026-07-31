<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mindigo\LearningTools\Http\Requests\ScoreScenarioRequest;
use Mindigo\LearningTools\Models\ScoreScenario;

class ScoreCalculatorController extends Controller
{
    public function index(Request $request): View
    {
        return view('learning-tools::scores.index', [
            'combinations' => config('learning-tools.score_combinations', []),
            'scenarios' => ScoreScenario::where('user_id', $request->user()->getAuthIdentifier())->latest()->get(),
        ]);
    }

    public function store(ScoreScenarioRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $scores = array_values($data['scores']);
        $total = min(30, array_sum($scores) + (float) ($data['priority_score'] ?? 0) + (float) ($data['bonus_score'] ?? 0));
        ScoreScenario::create([
            'user_id' => $request->user()->getAuthIdentifier(), 'title' => $data['title'],
            'combination_code' => $data['combination_code'], 'subject_scores' => $scores,
            'priority_score' => $data['priority_score'] ?? 0, 'bonus_score' => $data['bonus_score'] ?? 0,
            'total_score' => round($total, 2),
        ]);

        return back()->with('success', __('learning-tools::app.scores.created'));
    }

    public function destroy(Request $request, ScoreScenario $scenario): RedirectResponse
    {
        abort_unless((int) $scenario->user_id === (int) $request->user()->getAuthIdentifier(), 403);
        $scenario->delete();

        return back()->with('success', __('learning-tools::app.scores.deleted'));
    }
}
