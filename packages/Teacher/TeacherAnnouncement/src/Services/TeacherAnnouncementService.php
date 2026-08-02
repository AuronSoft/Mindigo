<?php

namespace Mindigo\TeacherAnnouncement\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\Notification\Notifications\AnnouncementPublished;
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
        $tid = $teacher->getAuthIdentifier();
        $base = Announcement::where('teacher_id', $tid);

        return [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->whereNotNull('published_at')->count(),
            'draft' => (clone $base)->whereNull('published_at')->count(),
            'pinned' => (clone $base)->where('is_pinned', true)->count(),
        ];
    }

    public function create(User $teacher, AnnouncementRequest $request): Announcement
    {
        $ann = Announcement::create([
            'teacher_id' => $teacher->getAuthIdentifier(),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'type' => $request->input('type', 'info'),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        if ($request->filled('classroom_ids')) {
            $ann->classrooms()->sync($request->input('classroom_ids'));
        }

        return $ann;
    }

    public function update(Announcement $ann, AnnouncementRequest $request): Announcement
    {
        $ann->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'type' => $request->input('type', 'info'),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        $ann->classrooms()->sync($request->input('classroom_ids', []));

        return $ann->refresh();
    }

    public function publish(Announcement $ann): void
    {
        $alreadyPublished = $ann->published_at !== null;

        $ann->update(['published_at' => now()]);

        // Chỉ bắn thông báo lần đầu phát hành
        if ($alreadyPublished) {
            return;
        }

        $this->notifyStudents($ann);
    }

    // Gửi thông báo tới học sinh đang học (active) ở các lớp được nhắm tới
    protected function notifyStudents(Announcement $ann): void
    {
        $ann->loadMissing('teacher:id,name');

        $classroomIds = $ann->classrooms()->pluck('classrooms.id');
        if ($classroomIds->isEmpty()) {
            return;
        }

        $studentIds = DB::table('classroom_students')
            ->whereIn('classroom_id', $classroomIds)
            ->where('status', 'active')
            ->pluck('student_id')
            ->unique();

        $students = User::whereIn('id', $studentIds)->get();
        if ($students->isEmpty()) {
            return;
        }

        Notification::send($students, new AnnouncementPublished(
            title: $ann->title,
            message: Str::limit(strip_tags($ann->content), 140),
            teacher: $ann->teacher?->name,
            url: Route::has('student.classrooms.index') ? route('student.classrooms.index') : null,
        ));
    }

    public function delete(Announcement $ann): void
    {
        $ann->delete();
    }

    public function myClassrooms(User $teacher): Collection
    {
        return Classroom::where('teacher_id', $teacher->getAuthIdentifier())
            ->where('status', 'active')
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }
}
