<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Mindigo\LearningTools\Http\Requests\PersonalizedPracticeSetRequest;
use Mindigo\StudentPractice\Models\PracticeSet;
use Mindigo\StudentPractice\Services\PracticeService;
use Mindigo\StudentPractice\Services\PracticeSetService;

class PersonalizedPracticeController extends Controller
{
    public function __construct(
        private readonly PracticeSetService $sets,
        private readonly PracticeService $practice,
    ) {}

    public function index(Request $request): View
    {
        return view('learning-tools::personalized.index', [
            'sets' => $this->sets->listFor($request->user()),
        ]);
    }

    public function create(Request $request): View
    {
        return view('learning-tools::personalized.form', $this->sets->formData($request->user()));
    }

    public function store(PersonalizedPracticeSetRequest $request): RedirectResponse
    {
        $set = $this->sets->create($request->user(), $request->validated());

        return to_route('learning-tools.personalized.show', $set)
            ->with('success', __('learning-tools::app.personalized.created'));
    }

    public function show(Request $request, PracticeSet $set): View
    {
        Gate::forUser($request->user())->authorize('view', $set);

        return view('learning-tools::personalized.show', [
            'set' => $this->sets->details($set),
        ]);
    }

    public function start(Request $request, PracticeSet $set): RedirectResponse
    {
        Gate::forUser($request->user())->authorize('start', $set);
        $attempt = $this->practice->startPracticeSet($request->user(), $set);

        return to_route('student.practice.attempt', $attempt);
    }

    public function destroy(Request $request, PracticeSet $set): RedirectResponse
    {
        Gate::forUser($request->user())->authorize('delete', $set);
        $this->sets->delete($set);

        return to_route('learning-tools.personalized.index')
            ->with('success', __('learning-tools::app.personalized.deleted'));
    }
}
