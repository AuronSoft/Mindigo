<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mindigo\LearningTools\Http\Requests\FocusSessionRequest;
use Mindigo\LearningTools\Models\FocusSession;
use Mindigo\SubjectManagement\Models\Subject;

class PomodoroController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->getAuthIdentifier();
        $activeSession = FocusSession::with('subject')->where('user_id', $userId)->where('status', 'running')->latest()->first();
        $sessions = FocusSession::with('subject')->where('user_id', $userId)->latest('started_at')->limit(10)->get();
        $weeklyMinutes = FocusSession::where('user_id', $userId)->where('status', 'completed')
            ->where('started_at', '>=', now()->startOfWeek())->sum('focus_minutes');

        return view('learning-tools::pomodoro.index', [
            'activeSession' => $activeSession,
            'sessions' => $sessions,
            'weeklyMinutes' => $weeklyMinutes,
            'subjects' => Subject::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(FocusSessionRequest $request): RedirectResponse
    {
        $userId = $request->user()->getAuthIdentifier();
        FocusSession::where('user_id', $userId)->where('status', 'running')->update([
            'status' => 'cancelled', 'ended_at' => now(),
        ]);

        FocusSession::create([
            ...$request->validated(),
            'user_id' => $userId,
            'started_at' => now(),
            'status' => 'running',
        ]);

        return to_route('learning-tools.pomodoro.index')->with('success', __('learning-tools::app.pomodoro.started'));
    }

    public function complete(Request $request, FocusSession $session): RedirectResponse
    {
        $this->authorizeOwner($request, $session);
        abort_unless($session->status === 'running', 422);

        $elapsedMinutes = max(1, min(
            $session->planned_minutes,
            (int) floor($session->started_at->diffInSeconds(now()) / 60)
        ));
        $session->update(['status' => 'completed', 'ended_at' => now(), 'focus_minutes' => $elapsedMinutes]);

        return to_route('learning-tools.pomodoro.index')->with('success', __('learning-tools::app.pomodoro.completed'));
    }

    public function cancel(Request $request, FocusSession $session): RedirectResponse
    {
        $this->authorizeOwner($request, $session);
        abort_unless($session->status === 'running', 422);
        $session->update(['status' => 'cancelled', 'ended_at' => now()]);

        return to_route('learning-tools.pomodoro.index')->with('success', __('learning-tools::app.pomodoro.cancelled'));
    }

    private function authorizeOwner(Request $request, FocusSession $session): void
    {
        abort_unless((int) $session->user_id === (int) $request->user()->getAuthIdentifier(), 403);
    }
}
