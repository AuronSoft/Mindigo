<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\Notification\Notifications\CourseAssigned;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\CourseLessonProgress;
use Mindigo\TeacherCourse\Models\Lesson;
use Tests\TestCase;

class CoursePublishingDistributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_duplicate_course_curriculum_as_a_draft(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = $this->course($teacher);
        [$first, $second] = $this->lessons($course, 2);
        $second->update(['prerequisite_lesson_id' => $first->id]);

        $this->actingAs($teacher)->post(route('teacher.courses.duplicate', $course))->assertRedirect();

        $copy = Course::query()->where('id', '!=', $course->id)->firstOrFail();
        $this->assertSame(Course::PUBLICATION_DRAFT, $copy->publication_status);
        $this->assertSame($teacher->id, $copy->teacher_id);
        $this->assertCount(2, $copy->lessons()->get());
        $copiedLessons = $copy->chapters()->firstOrFail()->lessons()->get();
        $this->assertSame($copiedLessons[0]->id, $copiedLessons[1]->prerequisite_lesson_id);
    }

    public function test_teacher_cannot_duplicate_or_reorder_another_teachers_course(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $outsider = $this->createUser(['role' => 'teacher']);
        $course = $this->course($owner);

        $this->actingAs($outsider)->post(route('teacher.courses.duplicate', $course))->assertForbidden();
        $this->actingAs($outsider)->patchJson(route('teacher.courses.curriculum.reorder', $course), ['chapters' => []])->assertForbidden();
    }

    public function test_owner_can_reorder_chapters_and_lessons(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = $this->course($teacher, Course::PUBLICATION_DRAFT);
        $chapterA = Chapter::query()->create(['course_id' => $course->id, 'name' => 'A', 'sort_order' => 0]);
        $chapterB = Chapter::query()->create(['course_id' => $course->id, 'name' => 'B', 'sort_order' => 1]);
        $lessonA = Lesson::query()->create(['chapter_id' => $chapterA->id, 'name' => 'A1', 'sort_order' => 0]);
        $lessonB = Lesson::query()->create(['chapter_id' => $chapterA->id, 'name' => 'A2', 'sort_order' => 1]);

        $this->actingAs($teacher)->patchJson(route('teacher.courses.curriculum.reorder', $course), [
            'chapters' => [
                ['id' => $chapterB->id, 'order' => 0, 'lessons' => []],
                ['id' => $chapterA->id, 'order' => 1, 'lessons' => [
                    ['id' => $lessonB->id, 'order' => 0], ['id' => $lessonA->id, 'order' => 1],
                ]],
            ],
        ])->assertOk()->assertJson(['saved' => true]);

        $this->assertSame(0, $chapterB->refresh()->sort_order);
        $this->assertSame(0, $lessonB->refresh()->sort_order);
        $this->assertSame(1, $lessonA->refresh()->sort_order);
    }

    public function test_distribution_metadata_is_updated_without_duplicate_enrollment_and_notifies_once(): void
    {
        Notification::fake();
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course($teacher);
        $classroom = $this->classroom($teacher, $student);
        $payload = ['classroom_ids' => [$classroom->id], 'starts_at' => now()->toDateString(), 'due_at' => now()->addWeek()->toDateString(), 'is_mandatory' => false, 'visibility' => 'visible'];

        $this->actingAs($teacher)->post(route('teacher.courses.assign', $course), $payload)->assertRedirect();
        $this->actingAs($teacher)->post(route('teacher.courses.assign', $course), [...$payload, 'is_mandatory' => true])->assertRedirect();

        $this->assertDatabaseCount('course_classroom_assignments', 1);
        $this->assertDatabaseCount('course_enrollments', 1);
        $this->assertDatabaseHas('course_classroom_assignments', ['course_id' => $course->id, 'classroom_id' => $classroom->id, 'is_mandatory' => true, 'visibility' => 'visible']);
        $this->assertNotNull(CourseEnrollment::query()->firstOrFail()->distribution_id);
        Notification::assertSentToTimes($student, CourseAssigned::class, 1);
    }

    public function test_hidden_or_future_distribution_is_not_available_to_student(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course($teacher);
        $classroom = $this->classroom($teacher, $student);

        $this->actingAs($teacher)->post(route('teacher.courses.assign', $course), ['classroom_ids' => [$classroom->id], 'visibility' => 'hidden'])->assertRedirect();
        $this->actingAs($student)->get(route('student.courses.show', $course->slug))->assertNotFound();
        $this->actingAs($student)->get(route('student.courses.index'))->assertSee(__('teacher-course::learning.empty_title'));

        $this->actingAs($teacher)->post(route('teacher.courses.assign', $course), ['classroom_ids' => [$classroom->id], 'visibility' => 'visible', 'starts_at' => now()->addDay()->toDateString()])->assertRedirect();
        $this->actingAs($student)->get(route('student.courses.show', $course->slug))->assertNotFound();
    }

    public function test_monitoring_reports_progress_only_to_course_owner(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $outsider = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course($teacher);
        $lesson = $this->lessons($course, 1)->first();
        $classroom = $this->classroom($teacher, $student);
        $this->actingAs($teacher)->post(route('teacher.courses.assign', $course), ['classroom_ids' => [$classroom->id]])->assertRedirect();
        $enrollment = CourseEnrollment::query()->firstOrFail();
        $enrollment->update(['status' => CourseEnrollment::STATUS_COMPLETED, 'completion_percentage' => 100, 'last_activity_at' => now()]);
        CourseLessonProgress::query()->create(['enrollment_id' => $enrollment->id, 'lesson_id' => $lesson->id, 'completed_at' => now()]);

        $this->actingAs($teacher)->get(route('teacher.courses.monitor', $course))->assertOk()->assertSee($student->name)->assertSee('100%');
        $this->actingAs($outsider)->get(route('teacher.courses.monitor', $course))->assertForbidden();
    }

    private function course(User $teacher, string $status = Course::PUBLICATION_PUBLISHED): Course
    {
        return Course::query()->create(['teacher_id' => $teacher->id, 'name' => 'Course '.str()->random(6), 'slug' => 'course-'.str()->lower(str()->random(10)), 'status' => 'active', 'is_active' => true, 'publication_status' => $status, 'published_at' => $status === Course::PUBLICATION_PUBLISHED ? now() : null, 'difficulty' => 'beginner', 'language' => 'vi']);
    }

    private function classroom(User $teacher, User $student): Classroom
    {
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'name' => 'Class '.str()->random(5), 'code' => str()->upper(str()->random(8)), 'slug' => 'class-'.str()->lower(str()->random(8)), 'status' => 'active']);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);

        return $classroom;
    }

    private function lessons(Course $course, int $count)
    {
        $chapter = Chapter::query()->create(['course_id' => $course->id, 'name' => 'Chapter', 'sort_order' => 0]);

        return collect(range(1, $count))->map(fn (int $index) => Lesson::query()->create(['chapter_id' => $chapter->id, 'name' => "Lesson {$index}", 'sort_order' => $index]));
    }
}
