<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseReviewHistory;

class AdminCourseReviewService
{
    public function __construct(private readonly CourseService $courses) {}

    public function queue(array $filters): LengthAwarePaginator
    {
        return Course::query()
            ->where('publication_status', Course::PUBLICATION_PENDING_REVIEW)
            ->with(['teacher:id,name,email', 'subject:id,name', 'category:id,name'])
            ->withCount(['chapters', 'lessons'])
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters): void {
                $search = '%'.trim($filters['search']).'%';
                $query->where(fn ($nested) => $nested
                    ->where('name', 'like', $search)
                    ->orWhereHas('teacher', fn ($teacher) => $teacher->where('name', 'like', $search)->orWhere('email', 'like', $search)));
            })
            ->when(filled($filters['teacher_id'] ?? null), fn ($query) => $query->where('teacher_id', $filters['teacher_id']))
            ->when(($filters['sort'] ?? 'newest') === 'oldest', fn ($query) => $query->orderBy('submitted_for_review_at'))
            ->when(($filters['sort'] ?? 'newest') === 'name', fn ($query) => $query->orderBy('name'))
            ->when(($filters['sort'] ?? 'newest') === 'newest', fn ($query) => $query->orderByDesc('submitted_for_review_at'))
            ->paginate(15)
            ->withQueryString();
    }

    public function teachers(): Collection
    {
        return User::query()->teachers()
            ->whereHas('taughtCourses', fn ($query) => $query->where('publication_status', Course::PUBLICATION_PENDING_REVIEW))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function detail(Course $course): Course
    {
        return $course->load([
            'teacher:id,name,email,avatar',
            'subject:id,name',
            'category:id,name',
            'chapters.lessons',
            'reviewHistory.reviewer:id,name',
        ])->loadCount(['chapters', 'lessons']);
    }

    public function approve(Course $course, User $admin, ?string $note = null): Course
    {
        return $this->courses->transition($course, Course::PUBLICATION_PUBLISHED, $admin, $note);
    }

    public function requestChanges(Course $course, User $admin, string $note): Course
    {
        return $this->courses->transition($course, Course::PUBLICATION_DRAFT, $admin, $note, true);
    }

    public function dashboard(): array
    {
        return [
            'pending' => Course::query()->where('publication_status', Course::PUBLICATION_PENDING_REVIEW)->count(),
            'approved_today' => CourseReviewHistory::query()
                ->where('review_status', CourseReviewHistory::STATUS_APPROVED)
                ->whereDate('reviewed_at', today())
                ->count(),
            'queue' => Course::query()
                ->where('publication_status', Course::PUBLICATION_PENDING_REVIEW)
                ->with('teacher:id,name')
                ->orderBy('submitted_for_review_at')
                ->limit(5)
                ->get(['id', 'teacher_id', 'name', 'submitted_for_review_at']),
        ];
    }
}
