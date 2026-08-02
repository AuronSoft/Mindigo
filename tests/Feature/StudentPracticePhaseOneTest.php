<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\SubjectManagement\Models\SubjectTopic;
use Tests\TestCase;

class StudentPracticePhaseOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_skill_and_attach_matching_approved_questions(): void
    {
        /** @var User $teacher */
        $teacher = $this->createUser(['role' => 'teacher']);
        [$subject, $topic] = $this->catalog('Mathematics');
        $matching = Question::factory()->create(['subject' => $subject->name, 'topic' => $topic->name]);
        $foreign = Question::factory()->create(['subject' => 'Physics']);

        $payload = [
            'subject_id' => $subject->id,
            'subject_topic_id' => $topic->id,
            'code' => 'math.linear-equations',
            'name' => 'Linear equations',
            'grade_level' => 'Grade 8',
            'status' => PracticeSkill::STATUS_ACTIVE,
            'question_ids' => [$matching->id, $foreign->id],
        ];
        $this->actingAs($teacher)->post(route('practice.skills.store'), $payload)
            ->assertSessionHasErrors('question_ids');

        $payload['question_ids'] = [$matching->id];
        $this->actingAs($teacher)->post(route('practice.skills.store'), $payload)
            ->assertRedirect(route('practice.skills.index'));

        $skill = PracticeSkill::query()->where('created_by', $teacher->id)->sole();
        $this->assertSame('MATH.LINEAR-EQUATIONS', $skill->code);
        $this->assertTrue($skill->questions->contains($matching));
        $this->assertFalse($skill->questions->contains($foreign));
        $this->assertSame(Question::PRACTICE_READY, $matching->fresh()->practice_status);
    }

    public function test_student_can_start_practice_by_active_skill_end_to_end(): void
    {
        /** @var User $teacher */
        $teacher = $this->createUser(['role' => 'teacher']);
        /** @var User $student */
        $student = $this->createUser(['role' => 'student']);
        [$subject, $topic] = $this->catalog('Chemistry');
        $question = Question::factory()->create([
            'subject' => $subject->name,
            'topic' => $topic->name,
            'content' => 'Skill-specific chemistry question',
        ]);
        $otherQuestion = Question::factory()->create([
            'subject' => $subject->name,
            'topic' => $topic->name,
            'content' => 'Chemistry question outside selected skill',
        ]);
        $skill = $this->skill($teacher, $subject, $topic);
        $skill->questions()->attach($question->id, ['is_primary' => true, 'weight' => 100]);

        $this->actingAs($student)
            ->get(route('student.practice.index'))
            ->assertOk()
            ->assertSee($skill->name)
            ->assertSee('data-mindigo-drawer-open="student-practice-filter"', false)
            ->assertSee('data-mindigo-drawer-panel="student-practice-filter"', false);

        $this->actingAs($student)
            ->get(route('student.practice.history'))
            ->assertOk()
            ->assertSee(__('student-practice::app.history_subtitle'))
            ->assertSee('aria-label="'.__('student-practice::app.back').'"', false);

        $this->actingAs($student)
            ->get(route('student.practice.index', [
                'keyword' => 'chemistry',
                'subject' => $subject->name,
                'topic' => $topic->name,
                'skill_id' => $skill->id,
                'difficulty' => $question->difficulty,
                'type' => $question->type,
            ]))
            ->assertOk()
            ->assertSee('Skill-specific chemistry question')
            ->assertDontSee('Chemistry question outside selected skill');

        $this->actingAs($student)->post(route('student.practice.start'), [
            'mode' => 'skill',
            'skill_id' => $skill->id,
            'question_count' => 1,
        ])->assertRedirect();

        $attempt = PracticeAttempt::query()->where('student_id', $student->id)->sole();
        $this->assertSame($skill->id, $attempt->practice_skill_id);
        $this->assertSame([$question->id], $attempt->answers()->pluck('question_id')->all());
        $this->assertNotSame($otherQuestion->id, $attempt->answers()->sole()->question_id);
    }

    public function test_inactive_skill_and_mismatched_topic_are_rejected(): void
    {
        /** @var User $teacher */
        $teacher = $this->createUser(['role' => 'teacher']);
        /** @var User $student */
        $student = $this->createUser(['role' => 'student']);
        [$subject] = $this->catalog('Biology');
        [, $foreignTopic] = $this->catalog('Literature');
        $skill = PracticeSkill::query()->create([
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'code' => 'BIO.CELL',
            'name' => 'Cells',
            'status' => PracticeSkill::STATUS_INACTIVE,
        ]);

        $this->actingAs($student)->post(route('student.practice.start'), [
            'mode' => 'skill',
            'skill_id' => $skill->id,
            'question_count' => 5,
        ])->assertSessionHasErrors('skill_id');

        $this->actingAs($teacher)->post(route('practice.skills.store'), [
            'subject_id' => $subject->id,
            'subject_topic_id' => $foreignTopic->id,
            'code' => 'BIO.INVALID',
            'name' => 'Invalid taxonomy',
            'status' => PracticeSkill::STATUS_ACTIVE,
        ])->assertSessionHasErrors('subject_topic_id');
    }

    public function test_teacher_cannot_modify_skill_owned_by_another_teacher(): void
    {
        /** @var User $owner */
        $owner = $this->createUser(['role' => 'teacher']);
        /** @var User $outsider */
        $outsider = $this->createUser(['role' => 'teacher']);
        [$subject, $topic] = $this->catalog('English');
        $skill = $this->skill($owner, $subject, $topic);

        $this->actingAs($outsider)
            ->get(route('practice.skills.edit', $skill))
            ->assertForbidden();

        $this->actingAs($outsider)->putJson(route('practice.skills.update', $skill), [
            'subject_id' => $subject->id,
            'code' => $skill->code,
            'name' => 'Tampered',
            'status' => PracticeSkill::STATUS_ACTIVE,
        ])->assertForbidden();

        $this->assertSame($skill->name, $skill->fresh()->name);
    }

    public function test_question_with_missing_answer_is_marked_for_review(): void
    {
        /** @var User $teacher */
        $teacher = $this->createUser(['role' => 'teacher']);
        [$subject, $topic] = $this->catalog('Geography');
        $question = Question::factory()->create([
            'subject' => $subject->name,
            'topic' => $topic->name,
            'correct_answers' => [],
        ]);

        $this->actingAs($teacher)->post(route('practice.skills.store'), [
            'subject_id' => $subject->id,
            'subject_topic_id' => $topic->id,
            'code' => 'GEO.MAP',
            'name' => 'Map reading',
            'status' => PracticeSkill::STATUS_ACTIVE,
            'question_ids' => [$question->id],
        ])->assertRedirect();

        $question->refresh();
        $this->assertSame(Question::PRACTICE_NEEDS_REVIEW, $question->practice_status);
        $this->assertContains('missing_correct_answer', $question->readiness_issues);
    }

    public function test_teacher_question_metadata_is_normalized_to_the_shared_catalog(): void
    {
        /** @var User $teacher */
        $teacher = $this->createUser(['role' => 'teacher']);
        [$subject, $topic] = $this->catalog('Civics');

        $this->actingAs($teacher)->post(route('teacher.questions.store'), [
            'subject' => $subject->name,
            'topic' => $topic->name,
            'type' => 'single_choice',
            'difficulty' => 'medium',
            'content' => 'Which action demonstrates civic responsibility?',
            'options' => ['Voting', 'Ignoring laws'],
            'correct_answer_single' => 'Voting',
            'grade_level' => 'Grade 9',
            'estimated_seconds' => 60,
            'hint' => 'Think about participation in public life.',
        ])->assertRedirect();

        $question = Question::query()->where('created_by', $teacher->id)->sole();
        $this->assertSame($subject->id, $question->subject_id);
        $this->assertSame($topic->id, $question->subject_topic_id);
        $this->assertSame('Grade 9', $question->grade_level);
        $this->assertSame(60, $question->estimated_seconds);
        $this->assertSame(Question::PRACTICE_NEEDS_REVIEW, $question->practice_status);
    }

    private function catalog(string $name): array
    {
        $subject = Subject::query()->create([
            'name' => $name,
            'code' => str($name)->upper()->replace(' ', '-')->toString(),
            'slug' => str($name)->slug()->toString(),
            'status' => 'active',
        ]);
        $topic = SubjectTopic::query()->create([
            'subject_id' => $subject->id,
            'name' => $name.' foundation',
            'slug' => 'foundation',
            'status' => 'active',
        ]);

        return [$subject, $topic];
    }

    private function skill(User $teacher, Subject $subject, SubjectTopic $topic): PracticeSkill
    {
        return PracticeSkill::query()->create([
            'subject_id' => $subject->id,
            'subject_topic_id' => $topic->id,
            'created_by' => $teacher->id,
            'code' => $subject->code.'.FOUNDATION',
            'name' => $subject->name.' foundation',
            'status' => PracticeSkill::STATUS_ACTIVE,
        ]);
    }
}
