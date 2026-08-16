<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Services\ExamService as ExamManagementService;
use Mindigo\StudentExam\Services\ExamService;
use Mindigo\TeacherClassroom\Models\Classroom;
use Tests\TestCase;

class ExamClassroomDistributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_only_receives_exams_assigned_to_an_active_classroom(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $otherStudent = $this->createUser(['role' => 'student']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id,
            'teacher_id' => $teacher->id,
            'name' => 'Lớp 12A1',
            'code' => '12A1',
            'slug' => 'lop-12a1',
            'status' => 'active',
        ]);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);

        $assignedExam = Exam::factory()->create([
            'audience' => ['roles' => ['student'], 'classrooms' => [$classroom->id]],
        ]);
        Exam::factory()->create([
            'audience' => ['roles' => ['student'], 'classrooms' => [$classroom->id + 999]],
        ]);

        $studentExams = app(ExamService::class)->getExamsForStudent($student->id);
        $otherStudentExams = app(ExamService::class)->getExamsForStudent($otherStudent->id);

        $this->assertTrue($studentExams['ongoing']->contains('id', $assignedExam->id));
        $this->assertCount(1, $studentExams['ongoing']);
        $this->assertTrue($otherStudentExams['ongoing']->isEmpty());
    }

    public function test_student_cannot_start_an_exam_from_an_unassigned_classroom(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $exam = Exam::factory()->create([
            'audience' => ['roles' => ['student'], 'classrooms' => [999]],
        ]);

        $this->assertFalse(app(ExamService::class)->isEnrolledInExamClassroom($exam, $student->id));
    }

    public function test_exam_builder_lists_only_owned_active_classrooms_with_active_student_count(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $otherTeacher = $this->createUser(['role' => 'teacher']);
        $activeStudent = $this->createUser(['role' => 'student']);
        $inactiveStudent = $this->createUser(['role' => 'student']);

        $owned = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'name' => 'Owned active', 'code' => 'OWN-ACTIVE', 'slug' => 'owned-active', 'status' => 'active']);
        $owned->students()->attach($activeStudent->id, ['status' => 'active', 'joined_at' => now()]);
        $owned->students()->attach($inactiveStudent->id, ['status' => 'inactive', 'joined_at' => now()]);
        Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'name' => 'Owned inactive', 'code' => 'OWN-INACTIVE', 'slug' => 'owned-inactive', 'status' => 'inactive']);
        Classroom::query()->create(['created_by' => $otherTeacher->id, 'teacher_id' => $otherTeacher->id, 'name' => 'Foreign active', 'code' => 'FOREIGN-ACTIVE', 'slug' => 'foreign-active', 'status' => 'active']);

        $classrooms = app(ExamManagementService::class)->formData($teacher)['classrooms'];

        $this->assertCount(1, $classrooms);
        $this->assertTrue($classrooms->first()->is($owned));
        $this->assertSame(1, $classrooms->first()->students_count);
    }
}
