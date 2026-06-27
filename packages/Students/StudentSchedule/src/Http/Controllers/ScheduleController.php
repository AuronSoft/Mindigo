<?php

namespace Mindigo\StudentSchedule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentSchedule\Services\ScheduleService;

class ScheduleController extends Controller
{
    public function __construct(protected ScheduleService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-schedule::index');
    }
}
