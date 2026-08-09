<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\TeacherAnnouncement\Models\Announcement;
use Tests\TestCase;

class StudentAnnouncementDetailTest extends TestCase
{
    use RefreshDatabase;

    private function classroom(array $attributes = []): Classroom
    {
        return Classroom::query()->create(array_merge([
            'name' => 'Toán 12A',
            'code' => 'MATH12A-'.uniqid(),
            'slug' => 'toan-12a-'.uniqid(),
            'status' => 'active',
        ], $attributes));
    }

    private function announcement(Classroom $classroom, array $attributes = []): Announcement
    {
        $ann = Announcement::query()->create(array_merge([
            'teacher_id' => $classroom->teacher_id ?? 1,
            'title' => 'Kiểm tra 15 phút tuần sau',
            'content' => 'Nội dung thông báo kiểm tra.',
            'type' => 'reminder',
            'is_pinned' => false,
            'published_at' => now(),
        ], $attributes));

        $ann->classrooms()->attach($classroom->id);

        return $ann;
    }

    public function test_student_in_target_classroom_can_view_announcement_detail(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom(['teacher_id' => $teacher->id]);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $ann = $this->announcement($classroom, ['teacher_id' => $teacher->id]);

        $this->actingAs($student)
            ->get(route('student.classrooms.announcements.show', [$classroom, $ann]))
            ->assertOk()
            ->assertSee($ann->title)
            ->assertSee('Kiểm tra')
            ->assertSee(__('student-classroom::app.type_reminder'))
            ->assertSee(route('student.classrooms.show', $classroom), false);
    }

    public function test_student_in_another_classroom_redirects_to_their_owned_classroom(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);

        $ownedClassroom = $this->classroom(['teacher_id' => $teacher->id]);
        $ownedClassroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);

        $otherClassroom = $this->classroom(['teacher_id' => $teacher->id]);
        $ann = $this->announcement($otherClassroom, ['teacher_id' => $teacher->id]);
        // Thông báo nhắm tới cả lớp khác mà học sinh thuộc
        $ann->classrooms()->attach($ownedClassroom->id);

        $this->actingAs($student)
            ->get(route('student.classrooms.announcements.show', [$otherClassroom, $ann]))
            ->assertRedirect(route('student.classrooms.announcements.show', [$ownedClassroom, $ann]));
    }

    public function test_student_not_in_any_target_classroom_is_forbidden(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $outsider = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom(['teacher_id' => $teacher->id]);
        $ann = $this->announcement($classroom, ['teacher_id' => $teacher->id]);

        $this->actingAs($outsider)
            ->get(route('student.classrooms.announcements.show', [$classroom, $ann]))
            ->assertForbidden();
    }

    public function test_unpublished_announcement_is_not_found(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom(['teacher_id' => $teacher->id]);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $ann = $this->announcement($classroom, ['teacher_id' => $teacher->id, 'published_at' => null]);

        $this->actingAs($student)
            ->get(route('student.classrooms.announcements.show', [$classroom, $ann]))
            ->assertNotFound();
    }
}
