<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Mindigo\LearningTools\Http\Requests\AcademicScoreRequest;
use Mindigo\LearningTools\Models\AcademicScoreScenario;

class AcademicScoreController extends Controller
{
    public function index(Request $request): View
    {
        return view('learning-tools::scores.academic', ['scenarios' => AcademicScoreScenario::where('user_id', $request->user()->getAuthIdentifier())->latest()->get()]);
    }

    public function store(AcademicScoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = collect($data['items'])->filter(fn (array $item) => filled($item['name'] ?? null) && isset($item['score'], $item['weight']))->map(fn (array $item) => ['name' => $item['name'], 'score' => (float) $item['score'], 'weight' => (float) $item['weight']])->values();
        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['items' => __('learning-tools::app.academic.no_items')]);
        }
        $result = round($items->sum(fn (array $item) => $item['score'] * $item['weight']) / $items->sum('weight') + (float) ($data['bonus_score'] ?? 0), 2);
        AcademicScoreScenario::create(['user_id' => $request->user()->getAuthIdentifier(), 'title' => $data['title'], 'type' => $data['type'], 'items' => $items->all(), 'bonus_score' => $data['bonus_score'] ?? 0, 'result' => $result]);

        return back()->with('success', __('learning-tools::app.academic.created'));
    }

    public function destroy(Request $request, AcademicScoreScenario $scenario): RedirectResponse
    {
        abort_unless((int) $scenario->user_id === (int) $request->user()->getAuthIdentifier(), 403);
        $scenario->delete();

        return back()->with('success', __('learning-tools::app.academic.deleted'));
    }
}
