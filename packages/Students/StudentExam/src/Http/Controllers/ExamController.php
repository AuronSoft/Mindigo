<?php

namespace Mindigo\StudentExam\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentExam\Services\ExamService;

class ExamController extends Controller
{
    public function __construct(protected ExamService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-exam::index');
    }
}
