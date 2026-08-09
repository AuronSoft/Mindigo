<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\Notification\Notifications\CourseAssigned;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\Lesson;
use Tests\TestCase;

class CourseEnrollmentLearningTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_self_enroll_once_and_open_my_courses(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course();

        $this->actingAs($student)->post(route('courses.enroll', $course->slug))
            ->assertRedirect(route('student.courses.show', $course->slug));
        $this->actingAs($student)->post(route('courses.enroll', $course->slug))->assertRedirect();

        $this->assertDatabaseCount('course_enrollments', 1);
        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id,
            'student_id' => $student->id,
            'status' => CourseEnrollment::STATUS_ENROLLED,
            'source' => 'self',
        ]);
        $this->actingAs($student)->get(route('student.courses.index'))->assertOk()->assertSee($course->name);
    }

    public function test_teacher_assigns_only_own_classroom_without_duplicate_enrollment_and_notifies_students(): void
    {
        Notification::fake();
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course($teacher);
        $classroom = $this->classroom($teacher, $student);

        $this->actingAs($teacher)->post(route('teacher.courses.assign', $course), [
            'classroom_ids' => [$classroom->id],
        ])->assertRedirect(route('teacher.courses.show', $course));
        $this->actingAs($teacher)->post(route('teacher.courses.assign', $course), [
            'classroom_ids' => [$classroom->id],
        ])->assertRedirect();

        $this->assertDatabaseCount('course_classroom_assignments', 1);
        $this->assertDatabaseCount('course_enrollments', 1);
        Notification::assertSentToTimes($student, CourseAssigned::class, 1);
    }

    public function test_teacher_cannot_assign_course_to_another_teachers_classroom(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $other = $this->createUser(['role' => 'teacher']);
        $course = $this->course($teacher);
        $classroom = $this->classroom($other, $this->createUser(['role' => 'student']));

        $this->actingAs($teacher)->post(route('teacher.courses.assign', $course), [
            'classroom_ids' => [$classroom->id],
        ])->assertSessionHasErrors('classroom_ids');

        $this->assertDatabaseCount('course_enrollments', 0);
    }

    public function test_student_cannot_access_another_students_enrollment_or_unenrolled_course(): void
    {
        $owner = $this->createUser(['role' => 'student']);
        $outsider = $this->createUser(['role' => 'student']);
        $course = $this->course();
        $this->enrollment($course, $owner);

        $this->actingAs($outsider)->get(route('student.courses.show', $course->slug))->assertNotFound();
        $this->actingAs($outsider)->get(route('student.courses.lessons.show', [$course->slug, $this->lessons($course, 1)->first()->id]))->assertNotFound();
    }

    public function test_lessons_must_be_opened_in_order_and_respect_prerequisite(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course();
        $lessons = $this->lessons($course, 3);
        $lessons[2]->update(['prerequisite_lesson_id' => $lessons[1]->id]);
        $this->enrollment($course, $student);

        $this->actingAs($student)->get(route('student.courses.lessons.show', [$course->slug, $lessons[1]->id]))
            ->assertSessionHasErrors('lesson');
        $this->actingAs($student)->get(route('student.courses.lessons.show', [$course->slug, $lessons[0]->id]))->assertOk();
        $this->actingAs($student)->post(route('student.courses.lessons.complete', [$course->slug, $lessons[0]->id]))->assertRedirect();
        $this->actingAs($student)->get(route('student.courses.lessons.show', [$course->slug, $lessons[1]->id]))->assertOk();
        $this->actingAs($student)->get(route('student.courses.lessons.show', [$course->slug, $lessons[2]->id]))
            ->assertSessionHasErrors('lesson');
    }

    public function test_continue_learning_uses_last_incomplete_lesson(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course();
        $lessons = $this->lessons($course, 2);
        $this->enrollment($course, $student);

        $this->actingAs($student)->get(route('student.courses.lessons.show', [$course->slug, $lessons[0]->id]))->assertOk();
        $this->actingAs($student)->get(route('student.courses.show', $course->slug))
            ->assertOk()
            ->assertSee(route('student.courses.lessons.show', [$course->slug, $lessons[0]->id]), false);
    }

    public function test_activity_and_completion_update_lesson_and_course_progress_idempotently(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course();
        $lessons = $this->lessons($course, 2);
        $this->enrollment($course, $student);

        $activityRoute = route('student.courses.lessons.activity', [$course->slug, $lessons[0]->id]);
        $this->actingAs($student)->postJson($activityRoute, ['seconds' => 30])->assertOk()->assertJson(['saved' => true]);
        $this->actingAs($student)->post(route('student.courses.lessons.complete', [$course->slug, $lessons[0]->id]))->assertRedirect();
        $this->actingAs($student)->post(route('student.courses.lessons.complete', [$course->slug, $lessons[0]->id]))->assertRedirect();

        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id, 'student_id' => $student->id,
            'completion_percentage' => 50, 'time_spent_seconds' => 30,
        ]);

        $this->actingAs($student)->post(route('student.courses.lessons.complete', [$course->slug, $lessons[1]->id]))->assertRedirect();
        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id, 'student_id' => $student->id,
            'completion_percentage' => 100, 'status' => CourseEnrollment::STATUS_COMPLETED,
        ]);
    }

    public function test_student_dashboard_is_synchronized_with_active_course_progress(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course();
        $enrollment = $this->enrollment($course, $student);
        $enrollment->update(['completion_percentage' => 42]);

        $this->actingAs($student)->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee($course->name)
            ->assertSee('42%');
    }

    private function course(?User $teacher = null): Course
    {
        $teacher ??= $this->createUser(['role' => 'teacher']);

        return Course::query()->create([
            'teacher_id' => $teacher->id, 'name' => 'Course '.str()->random(6),
            'slug' => 'course-'.str()->lower(str()->random(10)), 'status' => 'active', 'is_active' => true,
            'publication_status' => Course::PUBLICATION_PUBLISHED, 'published_at' => now(),
            'difficulty' => 'beginner', 'language' => 'vi', 'access_type' => 'free',
        ]);
    }

    private function classroom(User $teacher, User $student): Classroom
    {
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'name' => 'Class '.str()->random(5),
            'code' => str()->upper(str()->random(8)), 'slug' => 'class-'.str()->lower(str()->random(8)), 'status' => 'active',
        ]);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);

        return $classroom;
    }

    private function enrollment(Course $course, User $student): CourseEnrollment
    {
        return CourseEnrollment::query()->create([
            'course_id' => $course->id, 'student_id' => $student->id,
            'status' => CourseEnrollment::STATUS_ENROLLED, 'source' => 'self', 'enrolled_at' => now(),
        ]);
    }

    private function lessons(Course $course, int $count)
    {
        $chapter = Chapter::query()->create(['course_id' => $course->id, 'name' => 'Chapter']);

        return collect(range(1, $count))->map(fn (int $index) => Lesson::query()->create([
            'chapter_id' => $chapter->id, 'name' => "Lesson {$index}", 'content' => "Content {$index}", 'sort_order' => $index,
        ]));
    }
}
