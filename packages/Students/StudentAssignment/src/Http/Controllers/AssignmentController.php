<?php

namespace Mindigo\StudentAssignment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentAssignment\Services\AssignmentService;

class AssignmentController extends Controller
{
    public function __construct(protected AssignmentService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-assignment::index');
    }
}
