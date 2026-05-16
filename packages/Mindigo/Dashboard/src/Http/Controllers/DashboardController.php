<?php

namespace Mindigo\Dashboard\Http\Controllers;

use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('Mindigo-dashboard::dashboard');
    }
}