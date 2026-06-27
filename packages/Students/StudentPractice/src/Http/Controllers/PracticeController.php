<?php

namespace Mindigo\StudentPractice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentPractice\Services\PracticeService;

class PracticeController extends Controller
{
    public function __construct(protected PracticeService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-practice::index');
    }
}
