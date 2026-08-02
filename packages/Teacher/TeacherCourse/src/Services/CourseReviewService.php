<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\CourseReview;

class CourseReviewService
{
    public function moderationQueue(array $filters): LengthAwarePaginator
    {
        return CourseReview::query()->with(['course:id,name', 'student:id,name,email'])
            ->when(filled($filters['search'] ?? null), fn (Builder $query) => $query->where(function (Builder $search) use ($filters): void {
                $value = '%'.trim($filters['search']).'%';
                $search->where('comment', 'like', $value)->orWhereHas('student', fn (Builder $student) => $student->where('name', 'like', $value)->orWhere('email', 'like', $value))->orWhereHas('course', fn (Builder $course) => $course->where('name', 'like', $value));
            }))
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('moderation_status', $filters['status']))
            ->latest()->paginate(20)->withQueryString();
    }

    public function save(Course $course, User $student, array $data, ?CourseReview $review = null): CourseReview
    {
        return DB::transaction(function () use ($course, $student, $data, $review): CourseReview {
            Course::query()->whereKey($course)->lockForUpdate()->firstOrFail();
            $enrollment = CourseEnrollment::query()->where('course_id', $course->id)->where('student_id', $student->id)->lockForUpdate()->first();
            $minimum = max(0, min(100, (int) config('course.review_minimum_completion_percentage', 100)));
            if (! $enrollment || ($enrollment->status !== CourseEnrollment::STATUS_COMPLETED && $enrollment->completion_percentage < $minimum)) {
                throw ValidationException::withMessages(['review' => __('teacher-course::reviews.not_eligible')]);
            }
            if ($review && ((int) $review->enrollment_id !== (int) $enrollment->id || (int) $review->course_id !== (int) $course->id)) {
                abort(404);
            }

            $review ??= CourseReview::query()->where('enrollment_id', $enrollment->id)->lockForUpdate()->first();
            $review = $review
                ? tap($review)->update(['rating' => $data['rating'], 'comment' => $data['comment'] ?? null])
                : CourseReview::query()->create(['course_id' => $course->id, 'enrollment_id' => $enrollment->id, 'student_id' => $student->id, 'rating' => $data['rating'], 'comment' => $data['comment'] ?? null]);

            $this->syncRating($course);

            return $review->refresh();
        });
    }

    public function reply(CourseReview $review, User $teacher, string $reply): CourseReview
    {
        $review->update(['teacher_reply' => $reply, 'replied_by' => $teacher->id, 'replied_at' => now()]);

        return $review->refresh();
    }

    public function moderate(CourseReview $review, User $admin, array $data): CourseReview
    {
        return DB::transaction(function () use ($review, $admin, $data): CourseReview {
            Course::query()->whereKey($review->course_id)->lockForUpdate()->firstOrFail();
            $review->update(['moderation_status' => $data['moderation_status'], 'moderation_reason' => $data['moderation_status'] === CourseReview::STATUS_HIDDEN ? $data['moderation_reason'] : null, 'moderated_by' => $admin->id, 'moderated_at' => now()]);
            $this->syncRating($review->course);

            return $review->refresh();
        });
    }

    private function syncRating(Course $course): void
    {
        $summary = CourseReview::query()->where('course_id', $course->id)->visible()->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as average')->first();
        $course->update(['rating_count' => (int) $summary->total, 'rating_average' => round((float) $summary->average, 2)]);
    }
}
