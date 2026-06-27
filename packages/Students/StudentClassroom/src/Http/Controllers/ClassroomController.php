<?php

namespace Mindigo\StudentClassroom\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentClassroom\Services\ClassroomService;

class ClassroomController extends Controller
{
    public function __construct(protected ClassroomService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-classroom::index');
    }
}
