<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\ExamManagement\Http\Requests\ExamSessionRequest;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Services\ExamSessionService;

class ExamSessionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ExamSessionService $sessions) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ExamSession::class);

        return view('Mindigo-exam-management::sessions.index', ['sessions' => $this->sessions->listFor($request->user())->paginate(15)]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ExamSession::class);

        return view('Mindigo-exam-management::sessions.create', [
            'versions' => $this->sessions->readyVersions($request->user()),
            'classrooms' => $this->sessions->classrooms($request->user()),
        ]);
    }

    public function store(ExamSessionRequest $request): RedirectResponse
    {
        $this->sessions->create($request->user(), $request->validated());

        return redirect()->route('teacher.exam-sessions.index')->with('success', __('Mindigo-exam-management::app.session_builder.created'));
    }
}
