<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\Notification\Notifications\CourseReviewDecision;
use Mindigo\Notification\Notifications\CourseSubmittedForReview;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Mindigo\TeacherCourse\Models\CourseReviewHistory;

class CourseService
{
    private const SCHEDULE_FIELDS = ['starts_at', 'ends_at', 'schedule_days', 'study_time'];

    public function ownedList(User $user, array $filters): LengthAwarePaginator
    {
        return Course::query()
            ->when(! $user->isAdmin(), fn (Builder $query) => $query->where('teacher_id', $user->getAuthIdentifier()))
            ->with(['subject:id,name', 'category:id,name'])
            ->withCount(['chapters', 'lessons'])
            ->when(filled($filters['search'] ?? null), fn (Builder $query) => $query->where('name', 'like', '%'.trim($filters['search']).'%'))
            ->when(filled($filters['status'] ?? null), function (Builder $query) use ($filters): void {
                $query->where('is_active', $filters['status'] === 'active');
            })
            ->when(filled($filters['publication_status'] ?? null), fn (Builder $query) => $query->where('publication_status', $filters['publication_status']))
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    public function formData(): array
    {
        return [
            'subjects' => Subject::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'categories' => CourseCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function detail(Course $course): Course
    {
        return $course->load(['subject:id,name', 'category:id,name', 'chapters.lessons.assignment:id,title', 'chapters.lessons.prerequisite:id,name']);
    }

    public function create(User $teacher, array $data, ?UploadedFile $cover): Course
    {
        return DB::transaction(function () use ($teacher, $data, $cover): Course {
            $data['teacher_id'] = $teacher->getAuthIdentifier();
            $data['slug'] = $this->uniqueSlug($data['name']);
            $data['cover_image'] = $cover?->store('courses/covers', 'public');

            return Course::query()->create($this->normalize($data));
        });
    }

    public function update(Course $course, array $data, ?UploadedFile $cover, User $actor): Course
    {
        return DB::transaction(function () use ($course, $data, $cover, $actor): Course {
            $course = Course::query()->lockForUpdate()->findOrFail($course->id);
            $scheduleAction = $data['schedule_change_action'] ?? 'keep';
            unset($data['schedule_change_action']);
            $normalized = $this->normalize($data);
            $scheduleChanged = collect(self::SCHEDULE_FIELDS)->contains(
                fn (string $field) => $this->comparable($course->{$field}) !== $this->comparable($normalized[$field] ?? null)
            );

            $futureSchedules = $this->futureSchedules($course)->lockForUpdate()->get();
            if (($normalized['is_active'] ?? $course->is_active) === false && $futureSchedules->isNotEmpty() && $scheduleAction !== 'cancel_affected') {
                throw ValidationException::withMessages([
                    'schedule_change_action' => __('teacher-course::app.inactive_course_requires_cancellation'),
                ]);
            }

            if ($scheduleChanged && $futureSchedules->isNotEmpty() && $scheduleAction === 'align_future') {
                $this->assertCompleteSchedule($normalized);
            }

            if ($course->name !== $data['name']) {
                $data['slug'] = $this->uniqueSlug($data['name'], $course);
                $normalized['slug'] = $data['slug'];
            }

            if ($cover) {
                $oldCover = $course->cover_image;
                $data['cover_image'] = $cover->store('courses/covers', 'public');
                $normalized['cover_image'] = $data['cover_image'];
                DB::afterCommit(fn () => $oldCover && Storage::disk('public')->delete($oldCover));
            }

            $course->update($normalized);

            if ($futureSchedules->isNotEmpty()) {
                if ($scheduleAction === 'cancel_affected') {
                    $this->cancelAffectedSchedules($futureSchedules, $course, $scheduleChanged);
                } elseif ($scheduleChanged && $scheduleAction === 'align_future') {
                    $this->alignFutureSchedules($futureSchedules, $course, $actor);
                }
            }

            return $course->refresh();
        });
    }

    public function scheduleImpact(Course $course): array
    {
        $query = $this->futureSchedules($course);

        return [
            'classrooms' => (clone $query)->distinct()->count('classroom_id'),
            'sessions' => (clone $query)->count(),
        ];
    }

    public function delete(Course $course): void
    {
        DB::transaction(function () use ($course): void {
            $course = Course::query()->lockForUpdate()->findOrFail($course->id);
            if ($course->classrooms()->exists()) {
                throw ValidationException::withMessages([
                    'course' => __('teacher-course::app.course_has_linked_classrooms'),
                ]);
            }

            $cover = $course->cover_image;
            $course->delete();
            DB::afterCommit(fn () => $cover && Storage::disk('public')->delete($cover));
        });
    }

    public function transition(Course $course, string $status, User $actor, ?string $note = null, bool $changesRequested = false): Course
    {
        return DB::transaction(function () use ($course, $status, $actor, $note, $changesRequested): Course {
            $lockedCourse = Course::query()->lockForUpdate()->findOrFail($course->id);
            $previousState = $lockedCourse->publication_status;
            $allowedTransitions = [
                Course::PUBLICATION_DRAFT => [Course::PUBLICATION_PENDING_REVIEW, Course::PUBLICATION_ARCHIVED],
                Course::PUBLICATION_PENDING_REVIEW => [Course::PUBLICATION_DRAFT, Course::PUBLICATION_PUBLISHED, Course::PUBLICATION_ARCHIVED],
                Course::PUBLICATION_PUBLISHED => [Course::PUBLICATION_UNLISTED, Course::PUBLICATION_ARCHIVED],
                Course::PUBLICATION_UNLISTED => [Course::PUBLICATION_PENDING_REVIEW, Course::PUBLICATION_ARCHIVED],
                Course::PUBLICATION_ARCHIVED => [],
            ];

            if (! in_array($status, $allowedTransitions[$lockedCourse->publication_status] ?? [], true)) {
                throw ValidationException::withMessages([
                    'publication_status' => __('teacher-course::app.invalid_publication_transition'),
                ]);
            }

            if ($status === Course::PUBLICATION_ARCHIVED && $this->futureSchedules($lockedCourse)->lockForUpdate()->exists()) {
                throw ValidationException::withMessages([
                    'publication_status' => __('teacher-course::app.archive_course_has_future_sessions'),
                ]);
            }

            $attributes = ['publication_status' => $status];

            if ($status === Course::PUBLICATION_PENDING_REVIEW) {
                $attributes['submitted_for_review_at'] = now();
            }

            if ($status === Course::PUBLICATION_PUBLISHED) {
                $attributes['published_at'] = now();
                $attributes['published_by'] = $actor->getAuthIdentifier();
            }

            $lockedCourse->update($attributes);

            $reviewStatus = match (true) {
                $status === Course::PUBLICATION_PENDING_REVIEW => CourseReviewHistory::STATUS_PENDING,
                $status === Course::PUBLICATION_PUBLISHED => CourseReviewHistory::STATUS_APPROVED,
                $changesRequested => CourseReviewHistory::STATUS_CHANGES_REQUESTED,
                $status === Course::PUBLICATION_DRAFT => CourseReviewHistory::STATUS_WITHDRAWN,
                default => null,
            };

            if ($reviewStatus !== null) {
                CourseReviewHistory::query()->create([
                    'course_id' => $lockedCourse->id,
                    'reviewer_id' => $actor->isAdmin() ? $actor->id : null,
                    'review_status' => $reviewStatus,
                    'review_note' => $note,
                    'publication_state_before' => $previousState,
                    'publication_state_after' => $status,
                    'reviewed_at' => $actor->isAdmin() ? now() : null,
                ]);
            }

            $lockedCourse->loadMissing('teacher:id,name');
            if ($status === Course::PUBLICATION_PENDING_REVIEW) {
                $admins = User::query()->admins()->active()->get();
                DB::afterCommit(fn () => Notification::send($admins, new CourseSubmittedForReview(
                    $lockedCourse->id,
                    $lockedCourse->name,
                    $lockedCourse->teacher?->name ?? $actor->name,
                    route('admin.course-publication-reviews.show', $lockedCourse),
                )));
            } elseif ($status === Course::PUBLICATION_PUBLISHED || $changesRequested) {
                $decision = $status === Course::PUBLICATION_PUBLISHED
                    ? CourseReviewHistory::STATUS_APPROVED
                    : CourseReviewHistory::STATUS_CHANGES_REQUESTED;
                $teacher = $lockedCourse->teacher;
                DB::afterCommit(fn () => $teacher?->notify(new CourseReviewDecision(
                    $lockedCourse->id,
                    $lockedCourse->name,
                    $decision,
                    $note,
                    route('teacher.courses.show', $lockedCourse),
                )));
            }

            return $lockedCourse->refresh();
        });
    }

    public function duplicate(Course $course, User $actor): Course
    {
        return DB::transaction(function () use ($course, $actor): Course {
            $course->load('chapters.lessons');
            $copy = $course->replicate([
                'slug', 'publication_status', 'submitted_for_review_at', 'published_at', 'published_by',
                'view_count', 'enrollment_count', 'rating_average', 'rating_count',
            ]);
            $copy->teacher_id = $actor->isAdmin() ? $course->teacher_id : $actor->id;
            $copy->name = __('teacher-course::publishing.copy_name', ['name' => $course->name]);
            $copy->slug = $this->uniqueSlug($copy->name);
            $copy->publication_status = Course::PUBLICATION_DRAFT;
            $copy->cover_image = $this->copyStoredFile($course->cover_image, 'public', 'courses/covers');
            $copy->view_count = 0;
            $copy->enrollment_count = 0;
            $copy->rating_average = 0;
            $copy->rating_count = 0;
            $copy->save();

            $lessonMap = [];
            foreach ($course->chapters as $chapter) {
                $chapterCopy = $chapter->replicate();
                $chapterCopy->course_id = $copy->id;
                $chapterCopy->save();

                foreach ($chapter->lessons as $lesson) {
                    $lessonCopy = $lesson->replicate(['prerequisite_lesson_id', 'video_path', 'attachment_paths']);
                    $lessonCopy->chapter_id = $chapterCopy->id;
                    $lessonCopy->video_path = $this->copyStoredFile($lesson->video_path, 'local', 'course-content/videos');
                    $lessonCopy->attachment_paths = collect($lesson->attachment_paths ?? [])->map(function (array $attachment): array {
                        $disk = $attachment['disk'] ?? 'local';
                        $attachment['path'] = $this->copyStoredFile($attachment['path'] ?? null, $disk, 'course-content/attachments');

                        return $attachment;
                    })->filter(fn (array $attachment) => filled($attachment['path']))->values()->all() ?: null;
                    $lessonCopy->save();
                    $lessonMap[$lesson->id] = $lessonCopy;
                }
            }

            foreach ($course->chapters->flatMap->lessons as $lesson) {
                if ($lesson->prerequisite_lesson_id && isset($lessonMap[$lesson->prerequisite_lesson_id])) {
                    $lessonMap[$lesson->id]->update(['prerequisite_lesson_id' => $lessonMap[$lesson->prerequisite_lesson_id]->id]);
                }
            }

            return $copy;
        });
    }

    private function normalize(array $data): array
    {
        $data['is_active'] = ($data['status'] ?? 'active') === 'active';

        foreach (['learning_outcomes', 'requirements', 'target_learners'] as $field) {
            if (array_key_exists($field, $data) && is_string($data[$field])) {
                $data[$field] = collect(preg_split('/\r\n|\r|\n/', $data[$field]))
                    ->map(fn (string $value) => trim($value))
                    ->filter()
                    ->values()
                    ->all();
            }
        }

        $data['access_type'] ??= Course::ACCESS_TYPES[0];
        $data['currency'] ??= 'VND';
        $data['price'] = ($data['access_type'] ?? Course::ACCESS_TYPES[0]) === 'paid'
            ? (float) ($data['price'] ?? 0)
            : 0;

        if (array_key_exists('schedule_days', $data)) {
            $data['schedule_days'] = collect($data['schedule_days'] ?? [])
                ->intersect(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])
                ->values()
                ->all() ?: null;
        }

        unset($data['study_time_start'], $data['study_time_end']);

        return $data;
    }

    private function futureSchedules(Course $course): Builder
    {
        return ClassroomSchedule::query()
            ->whereHas('classroom', fn (Builder $query) => $query->where('course_id', $course->id))
            ->whereDate('session_date', '>=', today())
            ->whereIn('status', [ClassroomSchedule::STATUS_DRAFT, ClassroomSchedule::STATUS_SCHEDULED])
            ->orderBy('classroom_id')
            ->orderBy('session_date')
            ->orderBy('start_time');
    }

    private function comparable(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_array($value)) {
            sort($value);
        }

        return $value;
    }

    private function assertCompleteSchedule(array $data): void
    {
        if (blank($data['starts_at'] ?? null) || blank($data['schedule_days'] ?? null) || ! preg_match('/^(\d{2}:\d{2}) - (\d{2}:\d{2})$/', (string) ($data['study_time'] ?? ''))) {
            throw ValidationException::withMessages([
                'schedule_change_action' => __('teacher-course::app.schedule_sync_requires_complete_pattern'),
            ]);
        }
    }

    private function cancelAffectedSchedules(Collection $schedules, Course $course, bool $scheduleChanged): void
    {
        if ($course->is_active && ! $scheduleChanged) {
            return;
        }

        foreach ($schedules as $schedule) {
            if ($course->is_active && $schedule->type === ClassroomSchedule::TYPE_MAKEUP) {
                continue;
            }

            if ($course->is_active && $scheduleChanged && ! $this->violatesPattern($schedule, $course)) {
                continue;
            }

            $schedule->update([
                'status' => ClassroomSchedule::STATUS_CANCELLED,
                'cancel_reason' => __('teacher-course::app.course_schedule_change_cancel_reason'),
            ]);
        }
    }

    private function alignFutureSchedules(Collection $schedules, Course $course, User $actor): void
    {
        [$startTime, $endTime] = explode(' - ', $course->study_time, 2);
        $weekdays = collect($course->schedule_days)->map(fn (string $day) => array_search($day, ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'], true))->all();
        $regularSchedules = $schedules->where('type', ClassroomSchedule::TYPE_REGULAR)->values();
        $classrooms = Classroom::query()->whereIn('id', $regularSchedules->pluck('classroom_id')->unique())->lockForUpdate()->get()->keyBy('id');
        User::query()->whereIn('id', $classrooms->pluck('teacher_id')->merge($regularSchedules->pluck('substitute_teacher_id'))->filter()->unique())->orderBy('id')->lockForUpdate()->get();
        $plans = collect();

        foreach ($regularSchedules->groupBy('classroom_id') as $classroomSchedules) {
            $cursor = today()->max($course->starts_at)->startOfDay();
            foreach ($classroomSchedules as $schedule) {
                while (! in_array($cursor->dayOfWeek, $weekdays, true)) {
                    $cursor = $cursor->addDay();
                }
                if ($course->ends_at && $cursor->isAfter($course->ends_at)) {
                    throw ValidationException::withMessages([
                        'schedule_change_action' => __('teacher-course::app.schedule_sync_out_of_range'),
                    ]);
                }

                $plans->push([
                    'schedule' => $schedule,
                    'session_date' => $cursor->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);
                $cursor = $cursor->addDay();
            }
        }

        $batchIds = $regularSchedules->pluck('id');
        $plannedResources = [];
        foreach ($plans as $plan) {
            $schedule = $plan['schedule'];
            $classroom = $classrooms->get($schedule->classroom_id);
            $teacherIds = collect([$classroom->teacher_id, $schedule->substitute_teacher_id])->filter()->unique()->values();
            $resourceKeys = $teacherIds->map(fn (int $teacherId) => "teacher:{$teacherId}:{$plan['session_date']}:{$plan['start_time']}:{$plan['end_time']}")
                ->push("classroom:{$schedule->classroom_id}:{$plan['session_date']}:{$plan['start_time']}:{$plan['end_time']}");
            if ($resourceKeys->contains(fn (string $key) => isset($plannedResources[$key]))) {
                throw ValidationException::withMessages([
                    'schedule_change_action' => __('teacher-course::app.schedule_sync_conflict'),
                ]);
            }
            foreach ($resourceKeys as $key) {
                $plannedResources[$key] = true;
            }

            $conflict = ClassroomSchedule::query()
                ->whereNotIn('id', $batchIds)
                ->whereDate('session_date', $plan['session_date'])
                ->whereNotIn('status', [ClassroomSchedule::STATUS_CANCELLED, ClassroomSchedule::STATUS_RESCHEDULED])
                ->where('start_time', '<', $plan['end_time'].':00')
                ->where('end_time', '>', $plan['start_time'].':00')
                ->where(fn (Builder $query) => $query->where('classroom_id', $schedule->classroom_id)
                    ->orWhereIn('substitute_teacher_id', $teacherIds)
                    ->orWhereHas('classroom', fn (Builder $classrooms) => $classrooms->whereIn('teacher_id', $teacherIds)))
                ->exists();
            if ($conflict) {
                throw ValidationException::withMessages([
                    'schedule_change_action' => __('teacher-course::app.schedule_sync_conflict'),
                ]);
            }
        }

        ClassroomSchedule::query()->whereIn('id', $batchIds)->update(['slot_key' => null]);
        foreach ($plans as $plan) {
            $plan['schedule']->update([
                'session_date' => $plan['session_date'],
                'start_time' => $plan['start_time'],
                'end_time' => $plan['end_time'],
                'reschedule_reason' => __('teacher-course::app.course_schedule_sync_reason'),
                'updated_by' => $actor->id,
            ]);
        }
    }

    private function violatesPattern(ClassroomSchedule $schedule, Course $course): bool
    {
        $day = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'][$schedule->session_date->dayOfWeek];
        $outsideRange = ($course->starts_at && $schedule->session_date->isBefore($course->starts_at))
            || ($course->ends_at && $schedule->session_date->isAfter($course->ends_at));
        $wrongDay = ! in_array($day, $course->schedule_days ?? [], true);
        $wrongTime = $course->study_time && $course->study_time !== substr((string) $schedule->start_time, 0, 5).' - '.substr((string) $schedule->end_time, 0, 5);

        return $outsideRange || $wrongDay || $wrongTime;
    }

    private function uniqueSlug(string $name, ?Course $ignore = null): string
    {
        $base = Str::slug($name) ?: 'course';
        $slug = $base;
        $suffix = 1;

        while (Course::withTrashed()->where('slug', $slug)->when($ignore, fn (Builder $query) => $query->whereKeyNot($ignore))->exists()) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }

    private function copyStoredFile(?string $path, string $disk, string $directory): ?string
    {
        if (! $path || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $copy = $directory.'/'.Str::uuid().($extension ? '.'.$extension : '');

        return Storage::disk($disk)->copy($path, $copy) ? $copy : null;
    }
}
