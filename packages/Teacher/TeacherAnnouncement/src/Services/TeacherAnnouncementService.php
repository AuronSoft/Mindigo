<?php

namespace Mindigo\TeacherAnnouncement\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\TeacherAnnouncement\Http\Requests\AnnouncementRequest;
use Mindigo\TeacherAnnouncement\Models\Announcement;

class TeacherAnnouncementService
{
    public function list(User $teacher, array $filters = []): LengthAwarePaginator
    {
        $query = Announcement::where('teacher_id', $teacher->getAuthIdentifier())
            ->with('classrooms:id,name')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if (filled($filters['type'] ?? null)) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['published'])) {
            $filters['published']
                ? $query->whereNotNull('published_at')
                : $query->whereNull('published_at');
        }

        return $query->paginate(12)->withQueryString();
    }

    public function stats(User $teacher): array
    {
        $tid  = $teacher->getAuthIdentifier();
        $base = Announcement::where('teacher_id', $tid);

        return [
            'total'     => (clone $base)->count(),
            'published' => (clone $base)->whereNotNull('published_at')->count(),
            'draft'     => (clone $base)->whereNull('published_at')->count(),
            'pinned'    => (clone $base)->where('is_pinned', true)->count(),
        ];
    }

    public function create(User $teacher, AnnouncementRequest $request): Announcement
    {
        $ann = Announcement::create([
            'teacher_id' => $teacher->getAuthIdentifier(),
            'title'      => $request->input('title'),
            'content'    => $request->input('content'),
            'type'       => $request->input('type', 'info'),
            'is_pinned'  => $request->boolean('is_pinned'),
        ]);

        if ($request->filled('classroom_ids')) {
            $ann->classrooms()->sync($request->input('classroom_ids'));
        }

        return $ann;
    }

    public function update(Announcement $ann, AnnouncementRequest $request): Announcement
    {
        $ann->update([
            'title'     => $request->input('title'),
            'content'   => $request->input('content'),
            'type'      => $request->input('type', 'info'),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        $ann->classrooms()->sync($request->input('classroom_ids', []));

        return $ann->refresh();
    }

    public function publish(Announcement $ann): void
    {
        $ann->update(['published_at' => now()]);
    }

    public function delete(Announcement $ann): void
    {
        $ann->delete();
    }

    public function myClassrooms(User $teacher): \Illuminate\Support\Collection
    {
        return Classroom::where('teacher_id', $teacher->getAuthIdentifier())
            ->where('status', 'active')
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }
}
