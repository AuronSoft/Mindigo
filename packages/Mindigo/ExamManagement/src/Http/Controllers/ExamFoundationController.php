<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ExamFoundationController extends Controller
{
    public function __invoke(): View
    {
        return view('Mindigo-exam-management::foundation');
    }
}
