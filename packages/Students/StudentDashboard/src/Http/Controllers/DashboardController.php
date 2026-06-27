<?php

namespace Mindigo\StudentDashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentDashboard\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-dashboard::index');
    }
}
