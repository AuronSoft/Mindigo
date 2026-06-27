<?php

namespace Mindigo\StudentLeaderboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentLeaderboard\Services\LeaderboardService;

class LeaderboardController extends Controller
{
    public function __construct(protected LeaderboardService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-leaderboard::index');
    }
}
