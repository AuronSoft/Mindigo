<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\StudentExam\Services\ExamService;
use Tests\TestCase;

class ExamClassroomDistributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_only_receives_exams_assigned_to_an_active_classroom(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
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
        $student = User::factory()->create(['role' => 'student']);
        $exam = Exam::factory()->create([
            'audience' => ['roles' => ['student'], 'classrooms' => [999]],
        ]);

        $this->assertFalse(app(ExamService::class)->isEnrolledInExamClassroom($exam, $student->id));
    }
}
