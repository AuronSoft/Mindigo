<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\LearningTools\Models\AcademicScoreScenario;
use Mindigo\LearningTools\Models\AdmissionProgram;
use Mindigo\LearningTools\Models\GpaScenario;
use Mindigo\LearningTools\Models\ScoreScenario;
use Mindigo\LearningTools\Models\University;
use Tests\TestCase;

class LearningToolsPhaseFourTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_calculate_and_save_score_scenario(): void
    {
        $student = $this->createUser(['role' => 'student']);

        $this->actingAs($student)->post(route('learning-tools.scores.store'), [
            'title' => 'Target scenario', 'combination_code' => 'A00',
            'scores' => [8.5, 9, 7.5], 'priority_score' => 0.5, 'bonus_score' => 0.25,
        ])->assertRedirect();

        $scenario = ScoreScenario::where('user_id', $student->id)->sole();
        $this->assertSame(25.75, $scenario->total_score);
        $this->actingAs($student)->get(route('learning-tools.scores.index'))->assertOk()->assertSee('25.75');
    }

    public function test_score_scenarios_are_scoped_to_owner(): void
    {
        $owner = $this->createUser(['role' => 'student']);
        $outsider = $this->createUser(['role' => 'student']);
        $scenario = ScoreScenario::create([
            'user_id' => $owner->id, 'title' => 'Private target', 'combination_code' => 'D01',
            'subject_scores' => [8, 8, 8], 'priority_score' => 0, 'bonus_score' => 0, 'total_score' => 24,
        ]);

        $this->actingAs($outsider)->delete(route('learning-tools.scores.destroy', $scenario))->assertForbidden();
    }

    public function test_user_can_search_and_favorite_an_admission_program(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $university = University::create(['code' => 'MGO', 'name' => 'Mindigo University', 'province' => 'Ha Noi']);
        $program = AdmissionProgram::create([
            'university_id' => $university->id, 'major_code' => '7480201', 'major_name' => 'Information Technology',
            'year' => 2026, 'method' => 'Exam score', 'combinations' => ['A00', 'A01'], 'benchmark_score' => 26.5,
            'source_url' => 'https://example.edu.vn/admissions/2026', 'source_name' => 'Official admission announcement',
            'published_at' => now()->subDay(), 'verified_at' => now(), 'source_hash' => hash('sha256', 'official-record'),
        ]);

        $this->actingAs($student)->get(route('learning-tools.admissions.index', ['keyword' => 'Information']))
            ->assertOk()->assertSee('Mindigo University')->assertSee('26.50');
        $this->actingAs($student)->post(route('learning-tools.admissions.favorite', $program))->assertRedirect();
        $this->assertDatabaseHas('learning_admission_favorites', ['user_id' => $student->id, 'admission_program_id' => $program->id]);
    }

    public function test_unverified_admission_data_is_never_shown_or_favorited(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $university = University::create(['code' => 'UNVERIFIED', 'name' => 'Unverified University']);
        $program = AdmissionProgram::create(['university_id' => $university->id, 'major_name' => 'Unverified Major', 'year' => 2026, 'method' => 'Unknown']);

        $this->actingAs($student)->get(route('learning-tools.admissions.index'))->assertOk()->assertDontSee('Unverified Major');
        $this->actingAs($student)->post(route('learning-tools.admissions.favorite', $program))->assertNotFound();
    }

    public function test_phase_four_pages_require_authentication(): void
    {
        $this->get(route('learning-tools.scores.index'))->assertRedirect();
        $this->get(route('learning-tools.admissions.index'))->assertRedirect();
    }

    public function test_student_can_use_a_custom_school_score_formula(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $this->actingAs($student)->post(route('learning-tools.academic.store'), [
            'title' => 'Semester math', 'type' => 'subject_semester',
            'items' => [
                ['name' => 'Attendance', 'score' => 9, 'weight' => 1],
                ['name' => 'Midterm', 'score' => 8, 'weight' => 2],
                ['name' => 'Final', 'score' => 7, 'weight' => 3],
            ],
            'bonus_score' => 0,
        ])->assertRedirect();
        $this->assertSame(7.67, AcademicScoreScenario::where('user_id', $student->id)->sole()->result);
    }

    public function test_student_can_calculate_course_components_and_credit_gpa(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $this->actingAs($student)->post(route('learning-tools.gpa.store'), [
            'title' => 'Semester one',
            'courses' => [[
                'name' => 'Programming', 'credits' => 3,
                'components' => [
                    ['name' => 'Attendance', 'score' => 10, 'weight' => 10],
                    ['name' => 'Midterm', 'score' => 8, 'weight' => 30],
                    ['name' => 'Final', 'score' => 9, 'weight' => 60],
                ],
            ]],
        ])->assertRedirect();
        $scenario = GpaScenario::where('user_id', $student->id)->sole();
        $this->assertSame(8.8, $scenario->average_ten);
        $this->assertSame(4.0, $scenario->gpa_four);
        $this->assertSame('excellent', $scenario->classification);
        $this->actingAs($student)->get(route('learning-tools.gpa.index'))->assertOk()->assertSee(route('learning-tools.scores.index'));
        $this->actingAs($student)->get(route('learning-tools.academic.index'))->assertOk()->assertSee(route('learning-tools.scores.index'));
        $this->actingAs($student)->get(route('learning-tools.scores.index'))->assertOk()
            ->assertSee(route('learning-tools.academic.index'))->assertSee(route('learning-tools.gpa.index'));
    }
}
