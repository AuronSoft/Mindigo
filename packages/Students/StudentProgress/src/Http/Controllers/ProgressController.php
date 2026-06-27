<?php

namespace Mindigo\StudentProgress\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentProgress\Services\ProgressService;

class ProgressController extends Controller
{
    public function __construct(protected ProgressService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-progress::index');
    }
}
