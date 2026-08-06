<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Mindigo\TeacherCourse\Models\CourseReviewHistory;

class CourseService
{
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

    public function update(Course $course, array $data, ?UploadedFile $cover): Course
    {
        return DB::transaction(function () use ($course, $data, $cover): Course {
            if ($course->name !== $data['name']) {
                $data['slug'] = $this->uniqueSlug($data['name'], $course);
            }

            if ($cover) {
                $oldCover = $course->cover_image;
                $data['cover_image'] = $cover->store('courses/covers', 'public');
                DB::afterCommit(fn () => $oldCover && Storage::disk('public')->delete($oldCover));
            }

            $course->update($this->normalize($data));

            return $course->refresh();
        });
    }

    public function delete(Course $course): void
    {
        DB::transaction(function () use ($course): void {
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
