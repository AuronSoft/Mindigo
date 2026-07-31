<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mindigo\LearningTools\Http\Requests\GpaScenarioRequest;
use Mindigo\LearningTools\Models\GpaScenario;
use Mindigo\LearningTools\Services\GpaCalculatorService;

class GpaCalculatorController extends Controller
{
    public function index(Request $request): View
    {
        return view('learning-tools::gpa.index', ['scenarios' => GpaScenario::where('user_id', $request->user()->getAuthIdentifier())->latest()->get()]);
    }

    public function store(GpaScenarioRequest $request, GpaCalculatorService $calculator): RedirectResponse
    {
        $result = $calculator->calculate($request->validated('courses'));
        GpaScenario::create([...$result, 'user_id' => $request->user()->getAuthIdentifier(), 'title' => $request->validated('title')]);

        return back()->with('success', __('learning-tools::app.gpa.created'));
    }

    public function destroy(Request $request, GpaScenario $scenario): RedirectResponse
    {
        abort_unless((int) $scenario->user_id === (int) $request->user()->getAuthIdentifier(), 403);
        $scenario->delete();

        return back()->with('success', __('learning-tools::app.gpa.deleted'));
    }
}
