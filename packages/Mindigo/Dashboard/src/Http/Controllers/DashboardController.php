<?php

namespace Mindigo\Dashboard\Http\Controllers;

use Illuminate\Routing\Controller;
use Mindigo\Auth\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'students' => User::students()->count(),
            'teachers' => User::teachers()->count(),
            'admins' => User::admins()->count(),
            'active_users' => User::active()->count(),
        ];

        return view('Mindigo-dashboard::dashboard', compact('stats'));
    }
}
