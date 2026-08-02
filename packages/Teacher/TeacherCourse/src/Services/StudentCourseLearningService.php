<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\CourseLessonProgress;
use Mindigo\TeacherCourse\Models\Lesson;

class StudentCourseLearningService
{
    public function workspace(User $student, string $courseSlug): array
    {
        $enrollment = $this->enrollment($student, $courseSlug);
        $course = $enrollment->course;
        $lessons = $this->orderedLessons($enrollment);
        $completedIds = $enrollment->lessonProgress->whereNotNull('completed_at')->pluck('lesson_id');

        return [
            'enrollment' => $enrollment,
            'course' => $course,
            'lessons' => $lessons,
            'completedLessonIds' => $completedIds,
            'continueLesson' => $this->continueLesson($enrollment, $lessons, $completedIds),
        ];
    }

    public function openLesson(User $student, string $courseSlug, int $lessonId): array
    {
        return DB::transaction(function () use ($student, $courseSlug, $lessonId): array {
            $enrollment = $this->enrollment($student, $courseSlug, true);
            $lessons = $this->orderedLessons($enrollment);
            $lesson = $lessons->firstWhere('id', $lessonId);
            abort_unless($lesson, 404);
            Gate::forUser($student)->authorize('view', $lesson);

            $completedIds = CourseLessonProgress::query()
                ->where('enrollment_id', $enrollment->id)
                ->whereNotNull('completed_at')
                ->pluck('lesson_id');
            $this->assertSequenceAvailable($lessons, $lesson, $completedIds);

            $progress = CourseLessonProgress::query()->firstOrCreate(
                ['enrollment_id' => $enrollment->id, 'lesson_id' => $lesson->id],
                ['first_viewed_at' => now()],
            );
            $progress->forceFill(['last_viewed_at' => now()])->save();

            $enrollment->forceFill([
                'status' => $enrollment->status === CourseEnrollment::STATUS_COMPLETED
                    ? CourseEnrollment::STATUS_COMPLETED
                    : CourseEnrollment::STATUS_IN_PROGRESS,
                'started_at' => $enrollment->started_at ?? now(),
                'last_lesson_id' => $lesson->id,
                'last_activity_at' => now(),
            ])->save();

            return compact('enrollment', 'lesson', 'lessons', 'completedIds', 'progress');
        });
    }

    public function recordActivity(User $student, string $courseSlug, int $lessonId, int $seconds): CourseEnrollment
    {
        return DB::transaction(function () use ($student, $courseSlug, $lessonId, $seconds): CourseEnrollment {
            $data = $this->openLesson($student, $courseSlug, $lessonId);
            $enrollment = CourseEnrollment::query()->lockForUpdate()->findOrFail($data['enrollment']->id);
            $progress = CourseLessonProgress::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('lesson_id', $lessonId)
                ->lockForUpdate()
                ->firstOrFail();

            $progress->increment('time_spent_seconds', $seconds);
            $progress->forceFill(['last_viewed_at' => now()])->save();
            $enrollment->increment('time_spent_seconds', $seconds);
            $enrollment->forceFill(['last_activity_at' => now(), 'last_lesson_id' => $lessonId])->save();

            return $enrollment->refresh();
        });
    }

    public function completeLesson(User $student, string $courseSlug, int $lessonId): CourseEnrollment
    {
        return DB::transaction(function () use ($student, $courseSlug, $lessonId): CourseEnrollment {
            $data = $this->openLesson($student, $courseSlug, $lessonId);
            $enrollment = CourseEnrollment::query()->lockForUpdate()->findOrFail($data['enrollment']->id);
            $progress = CourseLessonProgress::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('lesson_id', $lessonId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $progress->completed_at) {
                $progress->forceFill(['completed_at' => now(), 'last_viewed_at' => now()])->save();
            }

            $total = $data['lessons']->count();
            $completed = CourseLessonProgress::query()
                ->where('enrollment_id', $enrollment->id)
                ->whereIn('lesson_id', $data['lessons']->pluck('id'))
                ->whereNotNull('completed_at')
                ->count();
            $percentage = $total > 0 ? (int) floor($completed / $total * 100) : 0;
            $isComplete = $total > 0 && $completed === $total;

            $enrollment->forceFill([
                'completion_percentage' => $percentage,
                'status' => $isComplete ? CourseEnrollment::STATUS_COMPLETED : CourseEnrollment::STATUS_IN_PROGRESS,
                'completed_at' => $isComplete ? ($enrollment->completed_at ?? now()) : null,
                'last_lesson_id' => $lessonId,
                'last_activity_at' => now(),
            ])->save();

            return $enrollment->refresh();
        });
    }

    private function enrollment(User $student, string $courseSlug, bool $lock = false): CourseEnrollment
    {
        return CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('status', CourseEnrollment::ACTIVE_STATUSES)
            ->availableToStudent()
            ->whereHas('course', fn ($query) => $query
                ->where('slug', $courseSlug)
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->whereIn('publication_status', [Course::PUBLICATION_PUBLISHED, Course::PUBLICATION_UNLISTED]))
            ->with([
                'course.teacher:id,name,avatar', 'course.subject:id,name', 'course.category:id,name',
                'course.chapters.lessons', 'lessonProgress', 'lastLesson', 'distribution',
            ])
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->firstOrFail();
    }

    private function orderedLessons(CourseEnrollment $enrollment): Collection
    {
        $course = $enrollment->course;
        $course->chapters->each(fn ($chapter) => $chapter->setRelation('course', $course));

        return $course->chapters->flatMap->lessons->values();
    }

    private function assertSequenceAvailable(Collection $lessons, Lesson $lesson, Collection $completedIds): void
    {
        $position = $lessons->search(fn (Lesson $candidate) => $candidate->is($lesson));
        $previousIds = $lessons->take($position)->pluck('id');
        $prerequisiteMet = ! $lesson->prerequisite_lesson_id || $completedIds->contains($lesson->prerequisite_lesson_id);

        if ($previousIds->diff($completedIds)->isNotEmpty() || ! $prerequisiteMet) {
            throw ValidationException::withMessages(['lesson' => __('teacher-course::learning.prerequisite_required')]);
        }
    }

    private function continueLesson(CourseEnrollment $enrollment, Collection $lessons, Collection $completedIds): ?Lesson
    {
        if ($enrollment->lastLesson && ! $completedIds->contains($enrollment->lastLesson->id)) {
            return $enrollment->lastLesson;
        }

        return $lessons->first(fn (Lesson $lesson) => ! $completedIds->contains($lesson->id)) ?? $lessons->last();
    }
}
