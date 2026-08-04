<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseView;
use Mindigo\TeacherCourse\Models\CourseWishlist;

class CourseDiscoveryService
{
    public function addWishlist(User $user, Course $course): void
    {
        abort_unless($course->isPublished(), 404);

        CourseWishlist::query()->firstOrCreate(['user_id' => $user->id, 'course_id' => $course->id]);
        $this->forgetUserRecommendations($user);
    }

    public function removeWishlist(User $user, Course $course): void
    {
        CourseWishlist::query()->whereBelongsTo($user)->whereBelongsTo($course)->delete();
        $this->forgetUserRecommendations($user);
    }

    public function wishlist(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return Course::query()->publiclyListed()
            ->whereHas('wishlists', fn ($query) => $query->where('user_id', $user->id))
            ->with(['teacher:id,name,avatar', 'subject:id,name', 'category:id,name'])
            ->withCount('lessons')->join('course_wishlists', fn ($join) => $join->on('course_wishlists.course_id', '=', 'courses.id')->where('course_wishlists.user_id', $user->id))
            ->latest('course_wishlists.created_at')->select('courses.*')
            ->paginate($perPage);
    }

    public function wishlistIds(?User $user): array
    {
        return $user?->isStudent()
            ? CourseWishlist::query()->where('user_id', $user->id)->pluck('course_id')->all()
            : [];
    }

    public function recordView(User $user, Course $course): void
    {
        if (! $user->isStudent() || ! $course->isPublished()) {
            return;
        }

        DB::transaction(function () use ($user, $course): void {
            $view = CourseView::query()->lockForUpdate()->firstOrNew([
                'user_id' => $user->id,
                'course_id' => $course->id,
            ]);
            $view->view_count = $view->exists ? $view->view_count + 1 : 1;
            $view->last_viewed_at = now();
            $view->save();
        });

        $this->forgetUserRecommendations($user);
    }

    public function recentlyViewed(User $user, int $limit = 8): Collection
    {
        return Course::query()->publiclyListed()
            ->whereHas('views', fn ($query) => $query->where('user_id', $user->id))
            ->with(['teacher:id,name,avatar', 'subject:id,name', 'category:id,name'])
            ->withCount('lessons')
            ->join('course_views', fn ($join) => $join->on('course_views.course_id', '=', 'courses.id')->where('course_views.user_id', $user->id))
            ->orderByDesc('course_views.last_viewed_at')->limit($limit)->select('courses.*')->get();
    }

    public function featured(int $limit = 8): Collection
    {
        return Cache::remember('courses:featured:'.$limit, $this->ttl(), fn () => Course::query()
            ->publiclyListed()->where('is_featured', true)
            ->with(['teacher:id,name,avatar', 'subject:id,name', 'category:id,name'])
            ->withCount('lessons')->orderBy('featured_order')->latest('featured_at')->limit($limit)->get());
    }

    public function trending(int $limit = 8): Collection
    {
        return Cache::remember('courses:trending:'.$limit, $this->ttl(), fn () => Course::query()
            ->publiclyListed()->with(['teacher:id,name,avatar', 'subject:id,name', 'category:id,name'])
            ->withCount('lessons')->orderByRaw('(view_count + enrollment_count * 4 + rating_count * 3) DESC')
            ->latest('published_at')->limit($limit)->get());
    }

    public function related(Course $course, int $limit = 6): Collection
    {
        return Course::query()->publiclyListed()->whereKeyNot($course->id)
            ->with(['teacher:id,name,avatar', 'subject:id,name', 'category:id,name'])->withCount('lessons')
            ->orderByRaw('(CASE WHEN subject_id = ? THEN 4 ELSE 0 END + CASE WHEN category_id = ? THEN 3 ELSE 0 END + CASE WHEN education_level = ? THEN 2 ELSE 0 END + CASE WHEN teacher_id = ? THEN 1 ELSE 0 END) DESC', [$course->subject_id, $course->category_id, $course->education_level, $course->teacher_id])
            ->latest('published_at')->limit($limit)->get();
    }

    public function setFeatured(Course $course, bool $featured, int $order = 0): Course
    {
        $course->update(['is_featured' => $featured, 'featured_order' => $order, 'featured_at' => $featured ? now() : null]);
        Cache::forget('courses:featured:'.config('course.discovery.section_limit', 8));

        return $course->refresh();
    }

    private function forgetUserRecommendations(User $user): void
    {
        Cache::forget('courses:recommendations:user:'.$user->id);
    }

    private function ttl(): int
    {
        return (int) config('course.discovery.cache_seconds', 600);
    }
}
