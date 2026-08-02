<?php

namespace Mindigo\TeacherAnnouncement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherAnnouncement\Http\Requests\AnnouncementRequest;
use Mindigo\TeacherAnnouncement\Models\Announcement;
use Mindigo\TeacherAnnouncement\Services\TeacherAnnouncementService;

class TeacherAnnouncementController extends Controller
{
    public function __construct(private readonly TeacherAnnouncementService $service) {}

    public function index()
    {
        session()->forget('url.intended');

        /** @var User $teacher */
        $teacher = Auth::user();
        $filters = request()->only(['type', 'published']);

        return view('teacher-announcement::index', [
            'announcements' => $this->service->list($teacher, $filters),
            'stats' => $this->service->stats($teacher),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        /** @var User $teacher */
        $teacher = Auth::user();

        return view('teacher-announcement::create', [
            'classrooms' => $this->service->myClassrooms($teacher),
            'types' => Announcement::TYPES,
        ]);
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        /** @var User $teacher */
        $teacher = Auth::user();
        $announcement = $this->service->create($teacher, $request);

        if ($request->boolean('publish_now')) {
            $this->service->publish($announcement);
        }

        return redirect()
            ->route('teacher.announcements.show', $announcement)
            ->with('success', __('teacher-announcement::app.created'));
    }

    public function show(Announcement $announcement)
    {
        $this->authorizeOwnership($announcement);
        $announcement->load('classrooms:id,name,students_count');

        return view('teacher-announcement::show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        $this->authorizeOwnership($announcement);

        /** @var User $teacher */
        $teacher = Auth::user();

        return view('teacher-announcement::edit', [
            'announcement' => $announcement,
            'classrooms' => $this->service->myClassrooms($teacher),
            'types' => Announcement::TYPES,
        ]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $this->authorizeOwnership($announcement);

        $this->service->update($announcement, $request);

        return redirect()
            ->route('teacher.announcements.show', $announcement)
            ->with('success', __('teacher-announcement::app.updated'));
    }

    public function publish(Announcement $announcement): RedirectResponse
    {
        $this->authorizeOwnership($announcement);

        $this->service->publish($announcement);

        return redirect()
            ->route('teacher.announcements.show', $announcement)
            ->with('success', __('teacher-announcement::app.published'));
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorizeOwnership($announcement);

        $this->service->delete($announcement);

        return redirect()
            ->route('teacher.announcements.index')
            ->with('success', __('teacher-announcement::app.deleted'));
    }

    private function authorizeOwnership(Announcement $announcement): void
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless(
            $user->isAdmin() || $announcement->teacher_id === (int) $user->getAuthIdentifier(),
            403
        );
    }
}
