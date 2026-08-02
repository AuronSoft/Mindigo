<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\Lesson;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseDetailService
{
    public function detail(Authenticatable $user, string $slug): Course
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->with([
                'teacher:id,name,avatar,bio',
                'teacher.teacherProfile:id,user_id,headline,biography,specialization,experience_years,qualifications,is_public',
                'subject:id,name,slug',
                'category:id,name,slug',
                'chapters:id,course_id,name,sort_order',
                'chapters.lessons:id,chapter_id,name,description,is_preview,sort_order',
            ])
            ->withCount(['chapters', 'lessons'])
            ->firstOrFail();

        abort_unless(Gate::forUser($user)->allows('viewDetail', $course), 404);

        $course->chapters->each(
            fn ($chapter) => $chapter->setRelation('course', $course)
        );

        if (method_exists($user, 'isStudent') && $user->isStudent()) {
            $course->load(['enrollments' => fn ($query) => $query->where('student_id', $user->getAuthIdentifier())]);
        }

        if ($course->isPublished()) {
            Course::query()->whereKey($course)->increment('view_count');
            $course->view_count++;
        }

        return $course;
    }

    public function lesson(Authenticatable $user, string $courseSlug, int $lessonId): Lesson
    {
        $lesson = $this->lessonQuery($courseSlug, $lessonId)
            ->with(['chapter.course', 'assignment:id,title'])
            ->firstOrFail();

        Gate::forUser($user)->authorize('view', $lesson);

        return $lesson;
    }

    public function video(Authenticatable $user, string $courseSlug, int $lessonId): StreamedResponse
    {
        $lesson = $this->lesson($user, $courseSlug, $lessonId);
        abort_if(blank($lesson->video_path), 404);

        return $this->fileResponse($lesson->video_path, basename($lesson->video_path), null, 'inline');
    }

    public function attachment(Authenticatable $user, string $courseSlug, int $lessonId, int $index): StreamedResponse
    {
        $lesson = $this->lesson($user, $courseSlug, $lessonId);
        $attachment = data_get($lesson->attachment_paths, $index);
        abort_unless(is_array($attachment) && filled($attachment['path'] ?? null), 404);

        return $this->fileResponse(
            $attachment['path'],
            $attachment['original_name'] ?? basename($attachment['path']),
            $attachment['mime'] ?? null,
            'attachment',
            $attachment['disk'] ?? null,
        );
    }

    private function lessonQuery(string $courseSlug, int $lessonId): Builder
    {
        return Lesson::query()
            ->whereKey($lessonId)
            ->whereHas('chapter.course', fn ($query) => $query->where('slug', $courseSlug));
    }

    private function fileResponse(
        string $path,
        string $name,
        ?string $mime,
        string $disposition,
        ?string $disk = null,
    ): StreamedResponse {
        $disk ??= str_starts_with($path, 'course-content/') ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path, $name, array_filter([
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition.'; filename="'.addslashes($name).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]));
    }
}
