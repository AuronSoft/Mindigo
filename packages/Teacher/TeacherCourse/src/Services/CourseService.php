<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseCategory;

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

    public function transition(Course $course, string $status, User $actor): Course
    {
        $allowedTransitions = [
            Course::PUBLICATION_DRAFT => [Course::PUBLICATION_PENDING_REVIEW, Course::PUBLICATION_ARCHIVED],
            Course::PUBLICATION_PENDING_REVIEW => [Course::PUBLICATION_DRAFT, Course::PUBLICATION_PUBLISHED, Course::PUBLICATION_ARCHIVED],
            Course::PUBLICATION_PUBLISHED => [Course::PUBLICATION_UNLISTED, Course::PUBLICATION_ARCHIVED],
            Course::PUBLICATION_UNLISTED => [Course::PUBLICATION_PENDING_REVIEW, Course::PUBLICATION_ARCHIVED],
            Course::PUBLICATION_ARCHIVED => [],
        ];

        if (! in_array($status, $allowedTransitions[$course->publication_status] ?? [], true)) {
            throw ValidationException::withMessages([
                'publication_status' => __('teacher-course::app.invalid_publication_transition'),
            ]);
        }

        return DB::transaction(function () use ($course, $status, $actor): Course {
            $attributes = ['publication_status' => $status];

            if ($status === Course::PUBLICATION_PENDING_REVIEW) {
                $attributes['submitted_for_review_at'] = now();
            }

            if ($status === Course::PUBLICATION_PUBLISHED) {
                $attributes['published_at'] = now();
                $attributes['published_by'] = $actor->getAuthIdentifier();
            }

            $course->update($attributes);

            return $course->refresh();
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
}
