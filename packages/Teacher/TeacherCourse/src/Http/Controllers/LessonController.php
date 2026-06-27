<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\Lesson;
use Mindigo\TeacherAssignment\Models\Assignment;

class LessonController extends Controller
{
    public function create(Course $course, Chapter $chapter)
    {
        $this->authorizeOwnership($course);

        $assignments = Assignment::query()
            ->where('teacher_id', Auth::id())
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title']);

        // All lessons in this course for prerequisite selection
        $existingLessons = $course->chapters->flatMap->lessons->pluck('name', 'id');

        return view('teacher-course::lessons.create', compact('course', 'chapter', 'assignments', 'existingLessons'));
    }

    public function store(Request $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'description'            => ['nullable', 'string'],
            'content'                => ['nullable', 'string'],
            'video'                  => ['nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:512000'],
            'attachments'            => ['nullable', 'array'],
            'attachments.*'          => ['file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
            'assignment_id'          => ['nullable', 'integer', 'exists:assignments,id'],
            'prerequisite_lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
        ]);

        $maxOrder = $chapter->lessons()->max('sort_order') ?? 0;

        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('courses/videos', 'public');
        }

        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachmentPaths[] = [
                    'path'         => $file->store('courses/attachments', 'public'),
                    'original_name' => $file->getClientOriginalName(),
                    'mime'         => $file->getMimeType(),
                    'size'         => $file->getSize(),
                ];
            }
        }

        $chapter->lessons()->create([
            'name'                   => $data['name'],
            'description'            => $data['description'] ?? null,
            'content'                => $data['content'] ?? null,
            'video_path'             => $videoPath,
            'attachment_paths'       => $attachmentPaths ?: null,
            'assignment_id'          => $data['assignment_id'] ?? null,
            'prerequisite_lesson_id' => $data['prerequisite_lesson_id'] ?? null,
            'sort_order'             => $maxOrder + 1,
        ]);

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', 'Đã thêm bài học mới.');
    }

    public function edit(Lesson $lesson)
    {
        $chapter = $lesson->chapter;
        $course  = $chapter->course;
        $this->authorizeOwnership($course);

        $assignments = Assignment::query()
            ->where('teacher_id', Auth::id())
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title']);

        $existingLessons = $course->chapters->flatMap->lessons
            ->where('id', '!=', $lesson->id)
            ->pluck('name', 'id');

        return view('teacher-course::lessons.edit', compact('course', 'chapter', 'lesson', 'assignments', 'existingLessons'));
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $chapter = $lesson->chapter;
        $course  = $chapter->course;
        $this->authorizeOwnership($course);

        $data = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'description'            => ['nullable', 'string'],
            'content'                => ['nullable', 'string'],
            'video'                  => ['nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:512000'],
            'remove_video'           => ['nullable', 'boolean'],
            'attachments'            => ['nullable', 'array'],
            'attachments.*'          => ['file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
            'remove_attachments'     => ['nullable', 'array'],
            'remove_attachments.*'   => ['string'],
            'assignment_id'          => ['nullable', 'integer', 'exists:assignments,id'],
            'prerequisite_lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
        ]);

        // Handle video
        $videoPath = $lesson->video_path;
        if ($request->boolean('remove_video') && $videoPath) {
            Storage::disk('public')->delete($videoPath);
            $videoPath = null;
        }
        if ($request->hasFile('video')) {
            if ($videoPath) {
                Storage::disk('public')->delete($videoPath);
            }
            $videoPath = $request->file('video')->store('courses/videos', 'public');
        }

        // Handle attachments
        $existingAttachments = $lesson->attachment_paths ?? [];
        $toRemove = $data['remove_attachments'] ?? [];
        if (!empty($toRemove)) {
            foreach ($existingAttachments as $key => $att) {
                if (in_array($att['path'], $toRemove)) {
                    Storage::disk('public')->delete($att['path']);
                    unset($existingAttachments[$key]);
                }
            }
            $existingAttachments = array_values($existingAttachments);
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $existingAttachments[] = [
                    'path'          => $file->store('courses/attachments', 'public'),
                    'original_name' => $file->getClientOriginalName(),
                    'mime'          => $file->getMimeType(),
                    'size'          => $file->getSize(),
                ];
            }
        }

        $lesson->update([
            'name'                   => $data['name'],
            'description'            => $data['description'] ?? null,
            'content'                => $data['content'] ?? null,
            'video_path'             => $videoPath,
            'attachment_paths'       => $existingAttachments ?: null,
            'assignment_id'          => $data['assignment_id'] ?? null,
            'prerequisite_lesson_id' => $data['prerequisite_lesson_id'] ?? null,
        ]);

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', 'Đã cập nhật bài học.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $chapter = $lesson->chapter;
        $course  = $chapter->course;
        $this->authorizeOwnership($course);

        // Clean up stored files
        if ($lesson->video_path) {
            Storage::disk('public')->delete($lesson->video_path);
        }
        if ($lesson->attachment_paths) {
            foreach ($lesson->attachment_paths as $att) {
                Storage::disk('public')->delete($att['path']);
            }
        }

        $lesson->delete();

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', 'Đã xóa bài học.');
    }

    private function authorizeOwnership(Course $course): void
    {
        $user = Auth::user();
        abort_unless(
            $user->isAdmin() || $course->teacher_id === (int) $user->getAuthIdentifier(),
            403,
            'Bạn không có quyền thao tác với bài học này.'
        );
    }
}
