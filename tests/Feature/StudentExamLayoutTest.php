<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class StudentExamLayoutTest extends TestCase
{
    public function test_student_exam_index_uses_the_registered_dashboard_layout(): void
    {
        $view = file_get_contents(base_path('packages/Students/StudentExam/src/resources/views/index.blade.php'));

        $this->assertIsString($view);
        $this->assertStringContainsString("@extends('Mindigo-dashboard::layouts')", $view);
        $this->assertStringNotContainsString("@extends('layouts.app')", $view);
    }

    public function test_student_exam_index_can_be_compiled(): void
    {
        $compiled = Blade::compileString(
            file_get_contents(base_path('packages/Students/StudentExam/src/resources/views/index.blade.php'))
        );

        $this->assertStringContainsString("make('Mindigo-dashboard::layouts'", $compiled);
    }
}
