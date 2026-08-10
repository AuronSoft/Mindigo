<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherClassroom\Models\Classroom;
use Tests\TestCase;

class ClassroomAssistantManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_teacher_can_assign_change_and_remove_an_active_teacher_as_assistant(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $firstAssistant = $this->createUser(['role' => 'teacher', 'is_active' => true]);
        $secondAssistant = $this->createUser(['role' => 'teacher', 'is_active' => true]);
        $subject = $this->subject();

        $this->actingAs($owner)->post(route('teacher.classrooms.store'), $this->payload($subject, [
            'assistant_id' => $firstAssistant->id,
        ]))->assertRedirect();

        $classroom = Classroom::query()->where('code', 'ASSIST-01')->firstOrFail();
        $this->assertSame($owner->id, $classroom->teacher_id);
        $this->assertSame($firstAssistant->id, $classroom->assistant_id);

        $this->actingAs($owner)->put(route('teacher.classrooms.update', $classroom), $this->payload($subject, [
            'assistant_id' => $secondAssistant->id,
        ]))->assertRedirect();

        $this->assertSame($owner->id, $classroom->fresh()->teacher_id);
        $this->assertSame($secondAssistant->id, $classroom->fresh()->assistant_id);

        $this->actingAs($owner)->put(route('teacher.classrooms.update', $classroom), $this->payload($subject, [
            'assistant_id' => null,
        ]))->assertRedirect();

        $this->assertNull($classroom->fresh()->assistant_id);
    }

    public function test_assistant_must_be_a_different_active_teacher(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $inactiveTeacher = $this->createUser(['role' => 'teacher', 'is_active' => false]);
        $student = $this->createUser(['role' => 'student', 'is_active' => true]);
        $subject = $this->subject();

        foreach ([$owner->id, $inactiveTeacher->id, $student->id] as $invalidAssistantId) {
            $this->actingAs($owner)->post(route('teacher.classrooms.store'), $this->payload($subject, [
                'assistant_id' => $invalidAssistantId,
            ]))->assertSessionHasErrors('assistant_id');
        }

        $this->assertDatabaseMissing('classrooms', ['code' => 'ASSIST-01']);
    }

    public function test_assistant_does_not_receive_classroom_owner_permissions(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $assistant = $this->createUser(['role' => 'teacher']);
        $subject = $this->subject();

        $this->actingAs($owner)->post(route('teacher.classrooms.store'), $this->payload($subject, [
            'assistant_id' => $assistant->id,
        ]));

        $classroom = Classroom::query()->where('code', 'ASSIST-01')->firstOrFail();

        $this->actingAs($assistant)->get(route('teacher.classrooms.show', $classroom))->assertForbidden();
        $this->actingAs($assistant)->get(route('teacher.classrooms.edit', $classroom))->assertForbidden();
        $this->actingAs($assistant)->put(route('teacher.classrooms.update', $classroom), $this->payload($subject))
            ->assertForbidden();
        $this->assertSame($owner->id, $classroom->fresh()->teacher_id);
    }

    private function subject(): Subject
    {
        return Subject::query()->create([
            'name' => 'Classroom assistance',
            'code' => 'ASSIST',
            'slug' => 'classroom-assistance',
            'color' => 'green',
            'status' => 'active',
            'sort_order' => 0,
        ]);
    }

    private function payload(Subject $subject, array $overrides = []): array
    {
        return [
            'name' => 'Assistant classroom',
            'code' => 'ASSIST-01',
            'school_year' => array_key_first(Classroom::schoolYearOptions()),
            'status' => 'active',
            'type' => Classroom::TYPE_STANDALONE,
            'subject_id' => $subject->id,
            ...$overrides,
        ];
    }
}
