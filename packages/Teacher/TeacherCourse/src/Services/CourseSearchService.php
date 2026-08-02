<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseSearch;

class CourseSearchService
{
    public function record(?User $user, ?string $keyword): void
    {
        $keyword = Str::of((string) $keyword)->squish()->lower()->limit(120, '')->toString();
        if ($keyword === '') {
            return;
        }

        CourseSearch::query()->create(['user_id' => $user?->id, 'keyword' => $keyword, 'searched_at' => now()]);
        Cache::forget('courses:search:popular');
    }

    public function suggestions(?string $term): Collection
    {
        $term = Str::of((string) $term)->squish()->toString();
        if (mb_strlen($term) < 2) {
            return collect();
        }

        return Course::query()->publiclyListed()->where(function ($query) use ($term): void {
            $query->where('name', 'like', '%'.$term.'%')
                ->orWhereHas('teacher', fn ($teacher) => $teacher->where('name', 'like', '%'.$term.'%'))
                ->orWhereHas('subject', fn ($subject) => $subject->where('name', 'like', '%'.$term.'%'))
                ->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$term.'%'));
        })
            ->orderByDesc('enrollment_count')->limit(8)->pluck('name');
    }

    public function popular(): Collection
    {
        return Cache::remember('courses:search:popular', (int) config('course.discovery.cache_seconds', 600), fn () => CourseSearch::query()
            ->where('searched_at', '>=', now()->subDays(30))->selectRaw('keyword, COUNT(*) AS total')
            ->groupBy('keyword')->orderByDesc('total')->limit(8)->pluck('keyword'));
    }

    public function recent(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return CourseSearch::query()->where('user_id', $user->id)->latest('searched_at')
            ->limit((int) config('course.discovery.recent_search_limit', 6) * 3)->pluck('keyword')->unique()->take(6)->values();
    }
}
