<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mindigo\TeacherCourse\Models\CourseCategory;

class CourseCategoryService
{
    public function paginated(array $filters): LengthAwarePaginator
    {
        return CourseCategory::query()
            ->withCount('courses')
            ->when(filled($filters['search'] ?? null), fn (Builder $query) => $query->where('name', 'like', '%'.trim($filters['search']).'%'))
            ->when(isset($filters['status']) && $filters['status'] !== '', fn (Builder $query) => $query->where('is_active', $filters['status'] === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): CourseCategory
    {
        return DB::transaction(function () use ($data): CourseCategory {
            $data['slug'] = $this->uniqueSlug($data['name']);

            return CourseCategory::query()->create($data);
        });
    }

    public function update(CourseCategory $category, array $data): CourseCategory
    {
        return DB::transaction(function () use ($category, $data): CourseCategory {
            if ($category->name !== $data['name']) {
                $data['slug'] = $this->uniqueSlug($data['name'], $category);
            }

            $category->update($data);

            return $category->refresh();
        });
    }

    public function delete(CourseCategory $category): void
    {
        DB::transaction(fn () => $category->delete());
    }

    private function uniqueSlug(string $name, ?CourseCategory $ignore = null): string
    {
        $base = Str::slug($name) ?: Str::random(8);
        $slug = $base;
        $suffix = 2;

        while (CourseCategory::query()->when($ignore, fn (Builder $query) => $query->whereKeyNot($ignore))->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
