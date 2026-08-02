<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\CourseLessonProgress;
use Mindigo\TeacherCourse\Models\CourseReview;

class CourseAnalyticsService
{
    public function teacher(User $teacher): array
    {
        return Cache::remember('course:analytics:teacher:'.$teacher->id, 300, function () use ($teacher): array {
            $courseIds = Course::query()->where('teacher_id', $teacher->id)->pluck('id');
            $enrollments = CourseEnrollment::query()->whereIn('course_id', $courseIds);
            $total = (clone $enrollments)->count();
            $completed = (clone $enrollments)->where('status', CourseEnrollment::STATUS_COMPLETED)->count();
            $lessonTotal = DB::table('course_enrollments')->join('courses', 'courses.id', '=', 'course_enrollments.course_id')
                ->join('chapters', 'chapters.course_id', '=', 'courses.id')->join('lessons', 'lessons.chapter_id', '=', 'chapters.id')
                ->whereIn('courses.id', $courseIds)->count();
            $lessonCompleted = CourseLessonProgress::query()->whereHas('enrollment', fn ($query) => $query->whereIn('course_id', $courseIds))->whereNotNull('completed_at')->count();
            $courseRows = Course::query()->whereKey($courseIds)->withCount('enrollments')->withAvg('enrollments', 'completion_percentage')->orderByDesc('enrollments_count')->get();

            return [
                'stats' => [
                    'total_courses' => $courseIds->count(), 'total_enrollments' => $total,
                    'in_progress' => (clone $enrollments)->where('status', CourseEnrollment::STATUS_IN_PROGRESS)->count(),
                    'completed' => $completed, 'completion_rate' => $this->rate($completed, $total),
                    'average_learning_time' => round((float) ((clone $enrollments)->avg('time_spent_seconds') ?? 0) / 60, 1),
                    'lesson_completion_rate' => $this->rate($lessonCompleted, $lessonTotal),
                    'chapter_completion_rate' => round((float) ((clone $enrollments)->avg('completion_percentage') ?? 0), 1),
                    'average_rating' => round((float) CourseReview::query()->whereIn('course_id', $courseIds)->visible()->avg('rating'), 1),
                ],
                'topCourses' => $courseRows->take(5),
                'lowestCourses' => $courseRows->sortBy('enrollments_avg_completion_percentage')->take(5)->values(),
                'reviewTrend' => $this->reviewTrend($courseIds->all()),
                'activityTimeline' => $this->activityTimeline($courseIds->all()),
            ];
        });
    }

    public function admin(): array
    {
        return Cache::remember('course:analytics:admin', 300, function (): array {
            $totalEnrollments = CourseEnrollment::query()->count();
            $completed = CourseEnrollment::query()->where('status', CourseEnrollment::STATUS_COMPLETED)->count();

            return [
                'stats' => [
                    'total_courses' => Course::query()->count(),
                    'published' => Course::query()->where('publication_status', Course::PUBLICATION_PUBLISHED)->count(),
                    'draft' => Course::query()->where('publication_status', Course::PUBLICATION_DRAFT)->count(),
                    'pending_review' => Course::query()->where('publication_status', Course::PUBLICATION_PENDING_REVIEW)->count(),
                    'archived' => Course::query()->where('publication_status', Course::PUBLICATION_ARCHIVED)->count(),
                    'featured' => Course::query()->where('is_featured', true)->count(),
                    'active_students' => CourseEnrollment::query()->where('last_activity_at', '>=', now()->subDays(30))->distinct()->count('student_id'),
                    'active_teachers' => Course::query()->where('updated_at', '>=', now()->subDays(30))->distinct()->count('teacher_id'),
                    'reviews' => CourseReview::query()->count(),
                    'average_rating' => round((float) CourseReview::query()->visible()->avg('rating'), 1),
                    'completion_rate' => $this->rate($completed, $totalEnrollments),
                ],
                'enrollmentGrowth' => $this->monthlyGrowth('course_enrollments'),
                'platformGrowth' => $this->monthlyGrowth('courses'),
                'topCategories' => $this->taxonomy('course_categories', 'category_id'),
                'topSubjects' => $this->taxonomy('subjects', 'subject_id'),
                'reviewTrend' => $this->reviewTrend(),
            ];
        });
    }

    public function activities(?User $teacher = null, int $perPage = 15): LengthAwarePaginator
    {
        return CourseEnrollment::query()->when($teacher, fn ($query) => $query->whereHas('course', fn ($course) => $course->where('teacher_id', $teacher->id)))
            ->with(['student:id,name,email', 'course:id,name,teacher_id'])->latest('last_activity_at')->paginate($perPage)->withQueryString();
    }

    private function reviewTrend(array $courseIds = []): array
    {
        return CourseReview::query()->when($courseIds !== [], fn ($query) => $query->whereIn('course_id', $courseIds))
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())->selectRaw('DATE(created_at) AS date, COUNT(*) AS total, AVG(rating) AS average')
            ->groupBy('date')->orderBy('date')->get()->map(fn ($row) => ['date' => $row->date, 'total' => (int) $row->total, 'average' => round((float) $row->average, 1)])->all();
    }

    private function activityTimeline(array $courseIds): array
    {
        return CourseEnrollment::query()->whereIn('course_id', $courseIds)->whereNotNull('last_activity_at')->where('last_activity_at', '>=', now()->subDays(29))
            ->selectRaw('DATE(last_activity_at) AS date, COUNT(*) AS total')->groupBy('date')->orderBy('date')->get()->map(fn ($row) => ['date' => $row->date, 'total' => (int) $row->total])->all();
    }

    private function monthlyGrowth(string $table): array
    {
        $period = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        return DB::table($table)->where('created_at', '>=', now()->subMonths(11)->startOfMonth())->selectRaw($period.' AS period, COUNT(*) AS total')
            ->groupBy('period')->orderBy('period')->get()->map(fn ($row) => ['period' => $row->period, 'total' => (int) $row->total])->all();
    }

    private function taxonomy(string $table, string $foreignKey)
    {
        return DB::table('courses')->join($table, $table.'.id', '=', 'courses.'.$foreignKey)->where('courses.publication_status', Course::PUBLICATION_PUBLISHED)
            ->selectRaw($table.'.name, COUNT(courses.id) AS courses_count, SUM(courses.enrollment_count) AS enrollments_count')->groupBy($table.'.id', $table.'.name')->orderByDesc('enrollments_count')->limit(8)->get();
    }

    private function rate(int $value, int $total): float
    {
        return $total > 0 ? round($value / $total * 100, 1) : 0;
    }
}
