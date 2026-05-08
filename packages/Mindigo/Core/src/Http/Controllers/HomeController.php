<?php

namespace Mindigo\Core\Http\Controllers;

use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('core::home');
    }
}