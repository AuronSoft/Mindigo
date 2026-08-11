<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\ExamManagement\Models\Exam;
use Tests\TestCase;

class TeacherExamIndexCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_exam_index_uses_catalog_cards_without_stat_cards(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $exam = Exam::query()->create([
            'created_by' => $teacher->id,
            'title' => 'Kiểm tra năng lực Toán học',
            'slug' => 'kiem-tra-nang-luc-toan-hoc',
            'subject' => 'Toán học',
            'topic' => 'Đại số',
            'status' => 'published',
            'total_questions' => 30,
        ]);

        $this->actingAs($teacher)->get(route('teacher.exams.index'))
            ->assertOk()
            ->assertSee(__('teacher-exam::app.catalog_title'))
            ->assertSee($exam->title)
            ->assertSee(__('teacher-exam::app.view_exam'))
            ->assertDontSee(__('teacher-exam::app.stat_total'));
    }

    public function test_exam_catalog_keeps_keyword_and_status_filters(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        foreach ([['Đề Toán', 'Toán', 'published'], ['Đề Văn', 'Ngữ văn', 'draft']] as [$title, $subject, $status]) {
            Exam::query()->create([
                'created_by' => $teacher->id,
                'title' => $title,
                'slug' => str($title)->slug().'-'.str()->random(4),
                'subject' => $subject,
                'status' => $status,
            ]);
        }

        $this->actingAs($teacher)->get(route('teacher.exams.index', ['keyword' => 'Toán', 'status' => 'published']))
            ->assertOk()
            ->assertSee('Đề Toán')
            ->assertDontSee('Đề Văn')
            ->assertSee(__('teacher-exam::app.clear_filter'));
    }
}
