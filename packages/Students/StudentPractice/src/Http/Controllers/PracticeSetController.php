<?php

namespace Mindigo\StudentPractice\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Mindigo\StudentPractice\Http\Requests\SavePracticeSetRequest;
use Mindigo\StudentPractice\Models\PracticeSet;
use Mindigo\StudentPractice\Services\PracticeService;
use Mindigo\StudentPractice\Services\PracticeSetService;

class PracticeSetController extends Controller
{
    public function __construct(
        private readonly PracticeSetService $sets,
        private readonly PracticeService $practice,
    ) {}

    public function index(Request $request): View
    {
        return view('student-practice::sets.index', [
            'sets' => $this->sets->listFor($request->user()),
            'formData' => $this->sets->formData($request->user()),
        ]);
    }

    public function store(SavePracticeSetRequest $request): RedirectResponse
    {
        $set = $this->sets->create($request->user(), $request->validated());

        return to_route('student.practice.sets.show', $set)
            ->with('success', __('student-practice::app.sets.saved'));
    }

    public function show(Request $request, PracticeSet $set): View
    {
        Gate::forUser($request->user())->authorize('view', $set);

        return view('student-practice::sets.show', ['set' => $this->sets->details($set)]);
    }

    public function shared(string $token): View
    {
        return view('student-practice::sets.shared', ['set' => $this->sets->findShared($token)]);
    }

    public function start(Request $request, PracticeSet $set): RedirectResponse
    {
        Gate::forUser($request->user())->authorize('start', $set);

        return to_route('student.practice.attempt', $this->practice->startPracticeSet($request->user(), $set));
    }

    public function repeat(Request $request, PracticeSet $set): RedirectResponse
    {
        Gate::forUser($request->user())->authorize('start', $set);

        return to_route('student.practice.attempt', $this->practice->startPracticeSet($request->user(), $set, true));
    }

    public function share(Request $request, PracticeSet $set): RedirectResponse
    {
        Gate::forUser($request->user())->authorize('update', $set);
        $this->sets->share($set, $request->boolean('enabled'));

        return back()->with('success', __('student-practice::app.sets.share_updated'));
    }

    public function destroy(Request $request, PracticeSet $set): RedirectResponse
    {
        Gate::forUser($request->user())->authorize('delete', $set);
        $this->sets->delete($set);

        return to_route('student.practice.sets.index')->with('success', __('student-practice::app.sets.deleted'));
    }
}
