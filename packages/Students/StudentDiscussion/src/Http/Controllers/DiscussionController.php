<?php

namespace Mindigo\StudentDiscussion\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentDiscussion\Services\DiscussionService;

class DiscussionController extends Controller
{
    public function __construct(protected DiscussionService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-discussion::index');
    }
}
