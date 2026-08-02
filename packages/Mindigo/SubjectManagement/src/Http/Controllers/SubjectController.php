<?php

namespace Mindigo\SubjectManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Http\Requests\SubjectRequest;
use Mindigo\SubjectManagement\Http\Requests\SubjectTopicRequest;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\SubjectManagement\Models\SubjectTopic;
use Mindigo\SubjectManagement\Services\SubjectManagementService;
use Symfony\Component\HttpFoundation\Response;

class SubjectController extends Controller
{
    public function __construct(private SubjectManagementService $subjects) {}

    public function index(Request $request)
    {
        $this->authorizePermission($request->user(), 'subjects.view');

        return view('Mindigo-subject-management::index', [
            'subjects' => $this->subjects->filteredList($request->only(['keyword', 'status'])),
            'stats' => $this->subjects->stats(),
            'statuses' => Subject::STATUSES,
            'filters' => $request->only(['keyword', 'status']),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizePermission($request->user(), 'subjects.create');

        return view('Mindigo-subject-management::create', [
            'statuses' => Subject::STATUSES,
            'colors' => Subject::COLORS,
        ]);
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        $subject = $this->subjects->create($request->user(), $request->validated());

        return redirect()
            ->route('subjects.show', $subject)
            ->with('success', __('Mindigo-subject-management::app.messages.created'));
    }

    public function show(Request $request, Subject $subject)
    {
        $this->authorizePermission($request->user(), 'subjects.view');
        $subject->load('topics');

        return view('Mindigo-subject-management::show', [
            'subject' => $subject,
            'usage' => $this->subjects->usageFor($subject),
            'statuses' => Subject::STATUSES,
            'topicStatuses' => SubjectTopic::STATUSES,
        ]);
    }

    public function edit(Request $request, Subject $subject)
    {
        $this->authorizePermission($request->user(), 'subjects.update');

        return view('Mindigo-subject-management::edit', [
            'subject' => $subject,
            'statuses' => Subject::STATUSES,
            'colors' => Subject::COLORS,
        ]);
    }

    public function update(SubjectRequest $request, Subject $subject): RedirectResponse
    {
        $this->subjects->update($subject, $request->validated());

        return redirect()
            ->route('subjects.show', $subject)
            ->with('success', __('Mindigo-subject-management::app.messages.updated'));
    }

    public function destroy(Request $request, Subject $subject): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'subjects.delete');
        $this->subjects->delete($subject);

        return redirect()
            ->route('subjects.index')
            ->with('success', __('Mindigo-subject-management::app.messages.deleted'));
    }

    public function storeTopic(SubjectTopicRequest $request, Subject $subject): RedirectResponse
    {
        $this->subjects->createTopic($subject, $request->validated());

        return back()->with('success', __('Mindigo-subject-management::app.messages.topic_created'));
    }

    public function updateTopic(SubjectTopicRequest $request, SubjectTopic $topic): RedirectResponse
    {
        $this->subjects->updateTopic($topic, $request->validated());

        return back()->with('success', __('Mindigo-subject-management::app.messages.topic_updated'));
    }

    public function destroyTopic(Request $request, SubjectTopic $topic): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'subjects.delete');
        $this->subjects->deleteTopic($topic);

        return back()->with('success', __('Mindigo-subject-management::app.messages.topic_deleted'));
    }

    private function authorizePermission(User $user, string $permission): void
    {
        if ($this->subjects->can($user, $permission)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}
