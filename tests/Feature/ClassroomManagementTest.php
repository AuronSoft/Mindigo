<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherClassroom\Models\ClassroomAttendance;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherClassroom\Services\TeacherClassroomService;
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
            'subject_ids' => [$subject->id],
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
            'subject_ids' => [$subjectB->id],
        ]);

        $response->assertRedirect();

        $classroom->refresh();
        $this->assertFalse($classroom->subjects->contains('id', $subjectA->id));
        $this->assertTrue($classroom->subjects->contains('id', $subjectB->id));
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
            'title' => 'Buá»•i há»c toÃ¡n (nÃ¢ng cao)',
            'session_date' => $schedule->session_date,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'description' => null,
        ]);

        $this->assertEquals('Buá»•i há»c toÃ¡n (nÃ¢ng cao)', $schedule->fresh()->title);
        $this->assertEquals('09:00', $schedule->fresh()->start_time);
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
}
