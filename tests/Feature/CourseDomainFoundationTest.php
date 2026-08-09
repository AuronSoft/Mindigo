<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\Lesson;
use Tests\TestCase;

class CourseDomainFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_cannot_view_or_modify_another_teachers_course(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $outsider = $this->createUser(['role' => 'teacher']);
        $course = $this->courseFor($owner);

        $this->actingAs($outsider)->get(route('teacher.courses.show', $course))->assertForbidden();
        $this->actingAs($outsider)->put(route('teacher.courses.update', $course), $this->coursePayload())->assertForbidden();
        $this->actingAs($outsider)->delete(route('teacher.courses.destroy', $course))->assertForbidden();

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'deleted_at' => null]);
    }

    public function test_scoped_binding_rejects_a_chapter_from_another_course(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = $this->courseFor($teacher, 'First course');
        $otherCourse = $this->courseFor($teacher, 'Second course');
        $foreignChapter = $this->chapterFor($otherCourse, 'Protected chapter');

        $this->actingAs($teacher)
            ->put(route('teacher.courses.chapters.update', [$course, $foreignChapter]), ['name' => 'Injected name'])
            ->assertNotFound();

        $this->assertDatabaseHas('chapters', ['id' => $foreignChapter->id, 'name' => 'Protected chapter']);
    }

    public function test_lesson_rejects_cross_course_prerequisite_and_cross_teacher_assignment(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $otherTeacher = $this->createUser(['role' => 'teacher']);
        $course = $this->courseFor($teacher, 'Owned course');
        $chapter = $this->chapterFor($course, 'Owned chapter');
        $otherCourse = $this->courseFor($otherTeacher, 'Foreign course');
        $foreignLesson = Lesson::query()->create([
            'chapter_id' => $this->chapterFor($otherCourse, 'Foreign chapter')->id,
            'name' => 'Foreign lesson',
            'sort_order' => 1,
        ]);
        $classroom = Classroom::query()->create([
            'created_by' => $otherTeacher->id,
            'teacher_id' => $otherTeacher->id,
            'name' => 'Foreign classroom',
            'code' => 'FOREIGN-CLASS',
            'slug' => 'foreign-class',
            'status' => 'active',
        ]);
        $assignment = Assignment::query()->create([
            'classroom_id' => $classroom->id,
            'teacher_id' => $otherTeacher->id,
            'title' => 'Foreign assignment',
            'due_date' => now()->addDay(),
            'status' => 'published',
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.courses.lessons.store', [$course, $chapter]), [
                'name' => 'Unsafe lesson',
                'assignment_id' => $assignment->id,
                'prerequisite_lesson_id' => $foreignLesson->id,
            ])
            ->assertSessionHasErrors(['assignment_id', 'prerequisite_lesson_id']);

        $this->assertDatabaseMissing('lessons', ['chapter_id' => $chapter->id, 'name' => 'Unsafe lesson']);
    }

    public function test_publication_lifecycle_requires_the_correct_actor(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $admin = $this->createUser(['role' => 'admin']);
        $course = $this->courseFor($teacher);

        $this->actingAs($teacher)->patch(route('teacher.courses.publication.update', $course), [
            'publication_status' => Course::PUBLICATION_PENDING_REVIEW,
        ])->assertRedirect(route('teacher.courses.show', $course));

        $course->refresh();
        $this->assertSame(Course::PUBLICATION_PENDING_REVIEW, $course->publication_status);
        $this->assertNotNull($course->submitted_for_review_at);

        $this->actingAs($teacher)->patch(route('teacher.courses.publication.update', $course), [
            'publication_status' => Course::PUBLICATION_PUBLISHED,
        ])->assertForbidden();

        $this->actingAs($admin)->patch(route('teacher.courses.publication.update', $course), [
            'publication_status' => Course::PUBLICATION_PUBLISHED,
        ])->assertRedirect(route('teacher.courses.show', $course));

        $course->refresh();
        $this->assertTrue($course->isPublished());
        $this->assertSame($admin->id, $course->published_by);
    }

    public function test_course_metadata_is_normalized_and_operational_status_is_separate(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)->post(route('teacher.courses.store'), [
            ...$this->coursePayload(),
            'status' => 'inactive',
            'education_level' => 'upper_secondary',
            'learning_outcomes' => "Outcome one\nOutcome two",
            'requirements' => "Requirement one\n",
            'target_learners' => 'Grade 12 students',
            'access_type' => 'paid',
            'price' => 750000,
            'currency' => 'VND',
            'starts_at' => '2026-09-15',
            'schedule_days' => ['mon', 'wed', 'fri'],
            'study_time' => '19:30 - 21:00',
        ])->assertRedirect();

        $course = Course::query()->where('name', 'Foundation course')->firstOrFail();
        $this->assertFalse($course->is_active);
        $this->assertSame(Course::PUBLICATION_DRAFT, $course->publication_status);
        $this->assertSame(['Outcome one', 'Outcome two'], $course->learning_outcomes);
        $this->assertSame(['Requirement one'], $course->requirements);
        $this->assertSame('paid', $course->access_type);
        $this->assertSame('750000.00', $course->price);
        $this->assertSame('VND', $course->currency);
        $this->assertSame('2026-09-15', $course->starts_at->format('Y-m-d'));
        $this->assertSame(['mon', 'wed', 'fri'], $course->schedule_days);
        $this->assertSame('19:30 - 21:00', $course->study_time);
    }

    private function courseFor(User $teacher, string $name = 'Foundation course'): Course
    {
        return Course::query()->create([
            'teacher_id' => $teacher->id,
            'name' => $name,
            'slug' => str($name)->slug().'-'.$teacher->id,
            'status' => 'active',
            'is_active' => true,
            'publication_status' => Course::PUBLICATION_DRAFT,
            'difficulty' => 'beginner',
            'language' => 'vi',
        ]);
    }

    private function chapterFor(Course $course, string $name): Chapter
    {
        return Chapter::query()->create(['course_id' => $course->id, 'name' => $name, 'sort_order' => 1]);
    }

    private function coursePayload(): array
    {
        return [
            'name' => 'Foundation course',
            'description' => 'Course description',
            'status' => 'active',
            'difficulty' => 'beginner',
            'language' => 'vi',
        ];
    }
}
