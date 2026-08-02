<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseCategory;

class CourseCatalogService
{
    public function catalog(array $filters): LengthAwarePaginator
    {
        $version = Course::query()->selectRaw('COUNT(*) AS aggregate_count, MAX(updated_at) AS latest_update')->first();
        $key = 'courses:catalog:'.sha1(json_encode([$filters, request()->integer('page', 1), $version?->aggregate_count, $version?->latest_update]));

        return Cache::remember($key, (int) config('course.discovery.cache_seconds', 600), fn () => $this->catalogQuery($filters)
            ->paginate(12)->withQueryString());
    }

    private function catalogQuery(array $filters): Builder
    {
        $query = Course::query()
            ->publiclyListed()
            ->with([
                'teacher:id,name,avatar',
                'teacher.teacherProfile:id,user_id,headline,is_public',
                'subject:id,name,slug',
                'category:id,name,slug',
            ])
            ->withCount('lessons')
            ->when(filled($filters['search'] ?? null), function (Builder $query) use ($filters): void {
                $search = trim($filters['search']);
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('teacher', fn (Builder $teacher) => $teacher->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('subject', fn (Builder $subject) => $subject->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(filled($filters['subject_id'] ?? null), fn (Builder $query) => $query->where('subject_id', $filters['subject_id']))
            ->when(filled($filters['category_id'] ?? null), fn (Builder $query) => $query->where('category_id', $filters['category_id']))
            ->when(filled($filters['education_level'] ?? null), fn (Builder $query) => $query->where('education_level', $filters['education_level']))
            ->when(filled($filters['difficulty'] ?? null), fn (Builder $query) => $query->where('difficulty', $filters['difficulty']));

        $this->applySort($query, $filters['sort'] ?? 'newest');

        return $query;
    }

    public function filters(): array
    {
        return Cache::remember('courses:catalog:filters', (int) config('course.discovery.cache_seconds', 600), fn () => [
            'subjects' => Subject::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'categories' => CourseCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'popular' => $query->orderByDesc('view_count')->orderByDesc('enrollment_count')->orderByDesc('id'),
            'rating' => $query->orderByDesc('rating_average')->orderByDesc('rating_count')->orderByDesc('id'),
            'enrolled' => $query->orderByDesc('enrollment_count')->orderByDesc('published_at')->orderByDesc('id'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };
    }
}
