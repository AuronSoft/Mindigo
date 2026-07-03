<?php

namespace Mindigo\Core\Http\Controllers;

use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('core::home');
    }

    public function terms()
    {
        return view('core::legal.terms', [
            'title' => 'Điều khoản sử dụng | Mindigo',
        ]);
    }
}
