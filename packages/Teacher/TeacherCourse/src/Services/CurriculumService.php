<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\Lesson;

class CurriculumService
{
    public function lessonFormData(Course $course, int $teacherId, ?Lesson $except = null): array
    {
        return [
            'assignments' => Assignment::query()
                ->where('teacher_id', $teacherId)
                ->where('status', 'published')
                ->orderBy('title')
                ->get(['id', 'title']),
            'existingLessons' => $course->chapters()
                ->with('lessons:id,chapter_id,name')
                ->get()
                ->flatMap->lessons
                ->when($except, fn ($lessons) => $lessons->where('id', '!=', $except->getKey()))
                ->pluck('name', 'id'),
        ];
    }

    public function createChapter(Course $course, array $data): Chapter
    {
        return DB::transaction(function () use ($course, $data): Chapter {
            $sortOrder = ((int) $course->chapters()->lockForUpdate()->max('sort_order')) + 1;

            return $course->chapters()->create(['name' => $data['name'], 'sort_order' => $sortOrder]);
        });
    }

    public function updateChapter(Chapter $chapter, array $data): Chapter
    {
        $chapter->update(['name' => $data['name']]);

        return $chapter->refresh();
    }

    public function deleteChapter(Chapter $chapter): void
    {
        DB::transaction(fn () => $chapter->delete());
    }

    public function createLesson(Chapter $chapter, array $data, ?UploadedFile $video, array $attachments): Lesson
    {
        return DB::transaction(function () use ($chapter, $data, $video, $attachments): Lesson {
            $sortOrder = ((int) $chapter->lessons()->lockForUpdate()->max('sort_order')) + 1;
            $data['sort_order'] = $sortOrder;
            $data['video_path'] = $video?->store('course-content/videos', 'local');
            $data['attachment_paths'] = $this->storeAttachments($attachments) ?: null;

            return $chapter->lessons()->create($data);
        });
    }

    public function updateLesson(
        Lesson $lesson,
        array $data,
        ?UploadedFile $video,
        array $attachments,
        bool $removeVideo,
    ): Lesson {
        return DB::transaction(function () use ($lesson, $data, $video, $attachments, $removeVideo): Lesson {
            $filesToDelete = [];
            $videoPath = $lesson->video_path;

            if (($removeVideo || $video) && $videoPath) {
                $filesToDelete[] = $videoPath;
                $videoPath = null;
            }

            if ($video) {
                $videoPath = $video->store('course-content/videos', 'local');
            }

            $existing = collect($lesson->attachment_paths ?? []);
            $remove = collect($data['remove_attachments'] ?? []);
            $filesToDelete = array_merge($filesToDelete, $existing->whereIn('path', $remove)->pluck('path')->all());
            $kept = $existing->whereNotIn('path', $remove)->values()->all();

            unset($data['remove_attachments']);
            $data['video_path'] = $videoPath;
            $data['attachment_paths'] = array_merge($kept, $this->storeAttachments($attachments)) ?: null;
            $lesson->update($data);

            DB::afterCommit(fn () => collect($filesToDelete)->each(fn (string $path) => $this->deleteStoredFile($path)));

            return $lesson->refresh();
        });
    }

    public function deleteLesson(Lesson $lesson): void
    {
        DB::transaction(function () use ($lesson): void {
            $files = collect($lesson->attachment_paths ?? [])->pluck('path')->filter();
            if ($lesson->video_path) {
                $files->push($lesson->video_path);
            }

            $lesson->delete();
            DB::afterCommit(fn () => $files->each(fn (string $path) => $this->deleteStoredFile($path)));
        });
    }

    private function storeAttachments(array $attachments): array
    {
        return collect($attachments)->map(fn (UploadedFile $file): array => [
            'path' => $file->store('course-content/attachments', 'local'),
            'disk' => 'local',
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ])->all();
    }

    private function deleteStoredFile(string $path): void
    {
        Storage::disk(str_starts_with($path, 'course-content/') ? 'local' : 'public')->delete($path);
    }
}
