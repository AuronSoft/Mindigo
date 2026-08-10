<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Services\TeacherClassroomService;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Tests\TestCase;

class ClassroomManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createSubject(string $name = 'ToÃ¡n'): Subject
    {
        return Subject::query()->create([
            'name' => $name,
            'code' => strtoupper(substr($name, 0, 3)),
            'slug' => strtolower($name),
            'color' => 'green',
            'status' => 'active',
            'sort_order' => 0,
        ]);
    }

    private function createClassroom(array $attributes = []): Classroom
    {
        $teacher = $attributes['teacher'] ?? $this->createUser(['role' => 'teacher']);
        $code = $attributes['code'] ?? 'C'.random_int(1000, 9999);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id,
            'teacher_id' => $teacher->id,
            'name' => $attributes['name'] ?? 'Lá»›p 10A1',
            'code' => $code,
            'slug' => $attributes['slug'] ?? strtolower($code),
            'status' => $attributes['status'] ?? 'active',
        ]);

        return $classroom;
    }

    public function test_teacher_can_create_classroom_with_subjects(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $subject = $this->createSubject();

        $response = $this->actingAs($teacher)->post(route('teacher.classrooms.store'), [
            'name' => 'Lá»›p 11B2',
            'code' => '11B2',
            'school_year' => '2025-2026',
            'status' => 'active',
            'type' => Classroom::TYPE_STANDALONE,
            'subject_id' => $subject->id,
        ]);

        $response->assertRedirect();

        $classroom = Classroom::query()->where('code', '11B2')->first();
        $this->assertNotNull($classroom);
        $this->assertEquals($teacher->id, $classroom->teacher_id);
        $this->assertEquals($teacher->id, $classroom->created_by);
        $this->assertTrue($classroom->subjects->contains('id', $subject->id));
    }

    public function test_teacher_can_update_classroom_and_sync_subjects(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $subjectA = $this->createSubject('ToÃ¡n');
        $subjectB = $this->createSubject('LÃ½');
        $classroom = $this->createClassroom(['teacher' => $teacher]);
        $classroom->subjects()->attach($subjectA->id);

        $response = $this->actingAs($teacher)->put(route('teacher.classrooms.update', $classroom), [
            'name' => 'Lá»›p 11B2 Ä‘á»•i',
            'code' => '11B2',
            'school_year' => '2025-2026',
            'status' => 'active',
            'type' => Classroom::TYPE_STANDALONE,
            'subject_id' => $subjectB->id,
        ]);

        $response->assertRedirect();

        $classroom->refresh();
        $this->assertFalse($classroom->subjects->contains('id', $subjectA->id));
        $this->assertTrue($classroom->subjects->contains('id', $subjectB->id));
        $this->assertSame($subjectB->id, $classroom->subject_id);
    }

    public function test_course_classroom_inherits_subject_and_creates_distribution(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $subject = $this->createSubject('Physics');
        $course = $this->createPublishedCourse($teacher, $subject, 'Physics Foundation');

        $this->actingAs($teacher)->post(route('teacher.classrooms.store'), [
            'name' => 'Physics Cohort A',
            'code' => 'PHY-A',
            'school_year' => '2026-2027',
            'status' => 'active',
            'type' => Classroom::TYPE_COURSE,
            'course_id' => $course->id,
        ])->assertRedirect();

        $classroom = Classroom::query()->where('code', 'PHY-A')->firstOrFail();
        $this->assertSame(Classroom::TYPE_COURSE, $classroom->type);
        $this->assertSame($course->id, $classroom->course_id);
        $this->assertSame($subject->id, $classroom->subject_id);
        $this->assertDatabaseHas('course_classroom_assignments', [
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
        ]);
    }

    public function test_teacher_cannot_link_another_teachers_course_or_override_course_subject(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $otherTeacher = $this->createUser(['role' => 'teacher']);
        $courseSubject = $this->createSubject('Chemistry');
        $otherSubject = $this->createSubject('Biology');
        $course = $this->createPublishedCourse($otherTeacher, $courseSubject, 'Chemistry Course');

        $this->actingAs($teacher)->post(route('teacher.classrooms.store'), [
            'name' => 'Invalid linked class',
            'code' => 'INVALID-LINK',
            'status' => 'active',
            'type' => Classroom::TYPE_COURSE,
            'course_id' => $course->id,
            'subject_id' => $otherSubject->id,
        ])->assertSessionHasErrors(['course_id', 'subject_id']);

        $this->assertDatabaseMissing('classrooms', ['code' => 'INVALID-LINK']);
    }

    public function test_course_classroom_student_sync_creates_and_withdraws_enrollment(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $subject = $this->createSubject('English');
        $course = $this->createPublishedCourse($teacher, $subject, 'English Course');
        $classroom = app(TeacherClassroomService::class)->create($teacher, [
            'name' => 'English Cohort', 'code' => 'ENG-A', 'status' => 'active',
            'type' => Classroom::TYPE_COURSE, 'course_id' => $course->id,
        ]);

        $this->actingAs($teacher)->post(route('teacher.classrooms.students.sync', $classroom), [
            'student_ids' => [$student->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id,
            'student_id' => $student->id,
            'classroom_id' => $classroom->id,
            'status' => CourseEnrollment::STATUS_INVITED,
        ]);

        $this->actingAs($teacher)->post(route('teacher.classrooms.students.sync', $classroom), [
            'student_ids' => [],
        ])->assertRedirect();

        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id,
            'student_id' => $student->id,
            'classroom_id' => null,
            'status' => CourseEnrollment::STATUS_WITHDRAWN,
        ]);
    }

    public function test_teacher_can_only_view_own_classrooms(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $otherTeacher = $this->createUser(['role' => 'teacher']);
        $own = $this->createClassroom(['teacher' => $teacher, 'code' => 'OWN1']);
        $this->createClassroom(['teacher' => $otherTeacher, 'code' => 'OTH1']);

        $response = $this->actingAs($teacher)->get(route('teacher.classrooms.index'));

        $response->assertOk();
        $response->assertSee('OWN1');
        $response->assertDontSee('OTH1');
    }

    public function test_teacher_cannot_access_another_teachers_classroom(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $otherTeacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->createClassroom(['teacher' => $otherTeacher]);

        $this->actingAs($teacher)->get(route('teacher.classrooms.show', $classroom))->assertForbidden();
        $this->actingAs($teacher)->get(route('teacher.classrooms.edit', $classroom))->assertForbidden();
    }

    public function test_admin_can_access_any_classroom(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->createClassroom(['teacher' => $teacher]);

        $this->actingAs($admin)->get(route('teacher.classrooms.show', $classroom))->assertOk();
    }

    public function test_teacher_can_delete_own_classroom(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->createClassroom(['teacher' => $teacher]);

        $response = $this->actingAs($teacher)->delete(route('teacher.classrooms.destroy', $classroom));

        $response->assertRedirect();
        $this->assertSoftDeleted('classrooms', ['id' => $classroom->id]);
    }

    public function test_stats_aggregate_correct_counts(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $studentA = $this->createUser(['role' => 'student']);
        $studentB = $this->createUser(['role' => 'student']);

        $active = $this->createClassroom(['teacher' => $teacher, 'status' => 'active']);
        $this->createClassroom(['teacher' => $teacher, 'status' => 'active']);
        $this->createClassroom(['teacher' => $teacher, 'status' => 'inactive']);

        $active->students()->attach($studentA->id, ['status' => 'active', 'joined_at' => now()]);
        $active->students()->attach($studentB->id, ['status' => 'active', 'joined_at' => now()]);

        $stats = app(TeacherClassroomService::class)->stats($teacher);

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['active']);
        $this->assertEquals(1, $stats['inactive']);
        $this->assertEquals(2, $stats['students']);
    }

    public function test_service_syncs_students(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->createClassroom(['teacher' => $teacher]);

        $this->actingAs($teacher)->post(route('teacher.classrooms.students.sync', $classroom), [
            'student_ids' => [$student->id],
        ])->assertRedirect();

        $this->assertTrue($classroom->students->contains('id', $student->id));
    }

    public function test_service_saves_and_retrieves_attendance(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->createClassroom(['teacher' => $teacher]);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $date = '2026-05-01';

        app(TeacherClassroomService::class)->saveAttendance($classroom, $date, [
            $student->id => ['status' => 'present', 'remarks' => null],
        ]);

        $records = app(TeacherClassroomService::class)->getAttendanceByDate($classroom, $date);
        $this->assertArrayHasKey($student->id, $records->all());
        $this->assertEquals('present', $records->get($student->id)->status);
    }

    public function test_service_creates_and_updates_schedule(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->createClassroom(['teacher' => $teacher]);
        $service = app(TeacherClassroomService::class);

        $schedule = $service->addSchedule($classroom, [
            'type' => 'regular',
            'title' => 'Buá»•i há»c toÃ¡n',
            'session_date' => now()->addDay()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'description' => 'Ã”n táº­p chÆ°Æ¡ng 1',
        ]);

        $this->assertDatabaseHas('classroom_schedules', [
            'id' => $schedule->id,
            'classroom_id' => $classroom->id,
            'title' => 'Buá»•i há»c toÃ¡n',
        ]);

        $service->updateSchedule($schedule, [
            'type' => 'regular',
            'title' => 'Buá»•i há»c toÃ¡n (nÃ¢ng cao)',
            'session_date' => $schedule->session_date,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'description' => null,
        ]);

        $this->assertEquals('Buá»•i há»c toÃ¡n (nÃ¢ng cao)', $schedule->fresh()->title);
        $this->assertEquals('09:00', $schedule->fresh()->start_time);
    }

    public function test_course_regular_schedule_must_follow_start_day_and_time_while_makeup_requires_reason(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $subject = $this->createSubject('History');
        $course = $this->createPublishedCourse($teacher, $subject, 'History Course');
        $course->update(['starts_at' => '2026-09-01', 'schedule_days' => ['mon'], 'study_time' => '08:00 - 10:00']);
        $classroom = app(TeacherClassroomService::class)->create($teacher, [
            'name' => 'History Cohort', 'code' => 'HIS-A', 'status' => 'active',
            'type' => Classroom::TYPE_COURSE, 'course_id' => $course->id,
        ]);

        $this->actingAs($teacher)->post(route('teacher.classrooms.schedules.store', $classroom), [
            'type' => 'regular', 'title' => 'Invalid early session', 'session_date' => '2026-08-31',
            'start_time' => '09:00', 'end_time' => '11:00',
        ])->assertSessionHasErrors(['session_date', 'start_time']);

        $this->actingAs($teacher)->post(route('teacher.classrooms.schedules.store', $classroom), [
            'type' => 'makeup', 'title' => 'Make-up session', 'session_date' => '2026-09-02',
            'start_time' => '09:00', 'end_time' => '11:00',
        ])->assertSessionHasErrors('makeup_reason');

        $this->actingAs($teacher)->post(route('teacher.classrooms.schedules.store', $classroom), [
            'type' => 'makeup', 'title' => 'Make-up session', 'session_date' => '2026-09-02',
            'start_time' => '09:00', 'end_time' => '11:00', 'makeup_reason' => 'Bù buổi học bị hoãn do mất điện.',
        ])->assertRedirect();

        $this->assertDatabaseHas('classroom_schedules', ['classroom_id' => $classroom->id, 'type' => 'makeup']);
    }

    public function test_student_can_check_in_with_active_code_only_once(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->createClassroom(['teacher' => $teacher]);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $session = app(TeacherClassroomService::class)->openCodeAttendance($classroom, $teacher, now()->toDateString(), 30);

        $this->actingAs($student)->post(route('student.classrooms.attendance.check-in', $classroom), [
            'attendance_code' => $session->code,
        ])->assertRedirect(route('student.classrooms.show', $classroom));

        $this->actingAs($student)->post(route('student.classrooms.attendance.check-in', $classroom), [
            'attendance_code' => $session->code,
        ])->assertRedirect();

        $this->assertDatabaseCount('classroom_attendances', 1);
        $this->assertDatabaseHas('classroom_attendances', [
            'classroom_id' => $classroom->id, 'student_id' => $student->id, 'method' => 'code', 'status' => 'present',
        ]);
    }

    public function test_student_cannot_check_in_with_wrong_or_expired_code(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->createClassroom(['teacher' => $teacher]);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $session = app(TeacherClassroomService::class)->openCodeAttendance($classroom, $teacher, now()->toDateString(), 30);

        $this->actingAs($student)->post(route('student.classrooms.attendance.check-in', $classroom), [
            'attendance_code' => 'WRONG1',
        ])->assertSessionHasErrors('attendance_code');

        $session->update(['expires_at' => now()->subMinute()]);
        $this->actingAs($student)->post(route('student.classrooms.attendance.check-in', $classroom), [
            'attendance_code' => $session->code,
        ])->assertSessionHasErrors('attendance_code');
        $this->assertDatabaseCount('classroom_attendances', 0);
    }

    public function test_student_can_view_only_classrooms_they_belong_to(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $otherStudent = $this->createUser(['role' => 'student']);
        $mine = $this->createClassroom(['teacher' => $teacher, 'code' => 'MY01']);
        $theirs = $this->createClassroom(['teacher' => $teacher, 'code' => 'THEIR01']);

        $mine->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);

        $response = $this->actingAs($student)->get(route('student.classrooms.index'));

        $response->assertOk();
        $response->assertSee('MY01');
        $response->assertDontSee('THEIR01');
    }

    private function createPublishedCourse($teacher, Subject $subject, string $name): Course
    {
        return Course::query()->create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'name' => $name,
            'slug' => str($name)->slug().'-'.uniqid(),
            'status' => 'active',
            'is_active' => true,
            'publication_status' => Course::PUBLICATION_PUBLISHED,
            'difficulty' => 'beginner',
            'language' => 'vi',
        ]);
    }
}
