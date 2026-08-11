<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherCourse\Models\Course;
use Tests\TestCase;

class AcademicCalendarPhaseTwentyTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_explains_the_impact_on_linked_future_sessions(): void
    {
        [$teacher, $course, $classroom] = $this->context();
        $this->classroomSession($classroom, '2026-09-14', '08:00', ClassroomSchedule::TYPE_REGULAR);

        $this->actingAs($teacher)->get(route('teacher.courses.edit', $course))
            ->assertOk()
            ->assertSee(__('teacher-course::app.schedule_impact_title'))
            ->assertSee('name="schedule_change_action"', false);
    }

    public function test_teacher_can_keep_or_align_future_regular_sessions_while_makeup_stays_fixed(): void
    {
        [$teacher, $course, $classroom] = $this->context();
        $regular = $this->classroomSession($classroom, '2026-09-14', '08:00', ClassroomSchedule::TYPE_REGULAR);
        $makeup = $this->classroomSession($classroom, '2026-09-15', '13:00', ClassroomSchedule::TYPE_MAKEUP);

        $this->actingAs($teacher)->put(route('teacher.courses.update', $course), $this->payload([
            'schedule_days' => ['wed'], 'study_time_start' => '09:00', 'study_time_end' => '11:00',
            'schedule_change_action' => 'keep',
        ]))->assertRedirect(route('teacher.courses.show', $course));
        $this->assertSame('2026-09-14', $regular->refresh()->session_date->toDateString());

        $this->actingAs($teacher)->put(route('teacher.courses.update', $course), $this->payload([
            'schedule_days' => ['fri'], 'study_time_start' => '10:00', 'study_time_end' => '12:00',
            'schedule_change_action' => 'align_future',
        ]))->assertRedirect(route('teacher.courses.show', $course));

        $regular->refresh();
        $this->assertSame(5, $regular->session_date->dayOfWeek);
        $this->assertSame('10:00', substr((string) $regular->start_time, 0, 5));
        $this->assertSame('2026-09-15', $makeup->refresh()->session_date->toDateString());
        $this->assertSame('13:00', substr((string) $makeup->start_time, 0, 5));
    }

    public function test_deactivation_requires_explicit_cancellation_and_cancels_all_future_sessions(): void
    {
        [$teacher, $course, $classroom] = $this->context();
        $regular = $this->classroomSession($classroom, '2026-09-14', '08:00', ClassroomSchedule::TYPE_REGULAR);
        $makeup = $this->classroomSession($classroom, '2026-09-15', '13:00', ClassroomSchedule::TYPE_MAKEUP);

        $this->actingAs($teacher)->from(route('teacher.courses.edit', $course))
            ->put(route('teacher.courses.update', $course), $this->payload(['status' => 'inactive']))
            ->assertSessionHasErrors('schedule_change_action');
        $this->assertTrue($course->refresh()->is_active);

        $this->actingAs($teacher)->put(route('teacher.courses.update', $course), $this->payload([
            'status' => 'inactive', 'schedule_change_action' => 'cancel_affected',
        ]))->assertRedirect(route('teacher.courses.show', $course));

        $this->assertFalse($course->refresh()->is_active);
        $this->assertSame(ClassroomSchedule::STATUS_CANCELLED, $regular->refresh()->status);
        $this->assertSame(ClassroomSchedule::STATUS_CANCELLED, $makeup->refresh()->status);
        $this->assertNotNull($regular->cancel_reason);
    }

    public function test_alignment_rolls_back_when_the_new_slot_conflicts_with_teacher_workload(): void
    {
        [$teacher, $course, $classroom] = $this->context();
        $regular = $this->classroomSession($classroom, '2026-09-14', '08:00', ClassroomSchedule::TYPE_REGULAR);
        $otherClassroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Other workload', 'code' => 'OTHER20', 'slug' => 'other-workload', 'status' => 'active',
        ]);
        $this->classroomSession($otherClassroom, '2026-09-04', '10:00', ClassroomSchedule::TYPE_REGULAR)
            ->update(['end_time' => '12:00']);

        $this->actingAs($teacher)->from(route('teacher.courses.edit', $course))
            ->put(route('teacher.courses.update', $course), $this->payload([
                'schedule_days' => ['fri'], 'study_time_start' => '10:00', 'study_time_end' => '12:00',
                'schedule_change_action' => 'align_future',
            ]))->assertSessionHasErrors('schedule_change_action');

        $this->assertSame(['mon'], $course->refresh()->schedule_days);
        $this->assertSame('2026-09-14', $regular->refresh()->session_date->toDateString());
        $this->assertSame('08:00', substr((string) $regular->start_time, 0, 5));
    }

    public function test_linked_course_cannot_be_deleted_and_relation_remains_intact(): void
    {
        [$teacher, $course, $classroom] = $this->context();

        $this->actingAs($teacher)->delete(route('teacher.courses.destroy', $course))
            ->assertSessionHasErrors('course');

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'deleted_at' => null]);
        $this->assertSame($course->id, $classroom->refresh()->course_id);
    }

    public function test_course_with_future_sessions_cannot_be_archived(): void
    {
        [$teacher, $course, $classroom] = $this->context();
        $this->classroomSession($classroom, '2026-09-14', '08:00', ClassroomSchedule::TYPE_REGULAR);

        $this->actingAs($teacher)->patch(route('teacher.courses.publication.update', $course), [
            'publication_status' => Course::PUBLICATION_ARCHIVED,
        ])->assertSessionHasErrors('publication_status');

        $this->assertSame(Course::PUBLICATION_DRAFT, $course->refresh()->publication_status);
    }

    private function context(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = Course::query()->create([
            'teacher_id' => $teacher->id, 'name' => 'Phase 20 course', 'slug' => 'phase-20-course',
            'status' => 'active', 'is_active' => true, 'publication_status' => Course::PUBLICATION_DRAFT,
            'difficulty' => 'beginner', 'language' => 'vi', 'starts_at' => '2026-09-01', 'ends_at' => '2026-12-31',
            'schedule_days' => ['mon'], 'study_time' => '08:00 - 10:00',
        ]);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_COURSE,
            'course_id' => $course->id, 'name' => 'Phase 20 classroom', 'code' => 'PH20',
            'slug' => 'phase-20-classroom', 'status' => 'active',
        ]);

        return [$teacher, $course, $classroom];
    }

    private function classroomSession(Classroom $classroom, string $date, string $start, string $type): ClassroomSchedule
    {
        return ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => $type, 'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE,
            'status' => ClassroomSchedule::STATUS_SCHEDULED, 'title' => $type.' session', 'session_date' => $date,
            'start_time' => $start, 'end_time' => $start === '13:00' ? '15:00' : '10:00',
            'makeup_reason' => $type === ClassroomSchedule::TYPE_MAKEUP ? 'Teacher absence' : null,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'Phase 20 course', 'description' => 'Updated course', 'status' => 'active',
            'difficulty' => 'beginner', 'language' => 'vi', 'starts_at' => '01/09/2026', 'ends_at' => '31/12/2026',
            'schedule_days' => ['mon'], 'study_time_start' => '08:00', 'study_time_end' => '10:00',
            'schedule_change_action' => 'keep', ...$overrides,
        ];
    }
}
