<?php

namespace Mindigo\StudentHistory\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentHistory\Services\HistoryService;

class HistoryController extends Controller
{
    public function __construct(protected HistoryService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-history::index');
    }
}
