<?php

namespace Mindigo\StudentNotebook\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentNotebook\Services\NotebookService;

class NotebookController extends Controller
{
    public function __construct(protected NotebookService $service)
    {
    }

    public function index(Request $request)
    {
        return view('student-notebook::index');
    }
}
