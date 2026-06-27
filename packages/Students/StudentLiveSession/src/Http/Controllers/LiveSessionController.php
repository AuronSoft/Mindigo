<?php

namespace Mindigo\StudentLiveSession\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentLiveSession\Services\LiveSessionService;

class LiveSessionController extends Controller
{
    public function __construct(protected LiveSessionService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-live-session::index');
    }
}
