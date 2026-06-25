<?php

namespace Mindigo\Auth\Http\Controllers;

use App\Support\RoleRedirector;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\Auth\Http\Requests\LoginRequest;
use Mindigo\Auth\Services\LoginService;

class LoginController extends Controller
{
    public function __construct(
        private readonly LoginService $service
    ) {}

    public function index()
    {
        return view('Mindigo-auth::login');
    }

    public function store(LoginRequest $request)
    {
        $this->service->checkThrottle($request);
        $this->service->attempt($request);
        $this->service->persistSession($request);

        $user = auth()->user();

        if ($user?->role === 'admin') {
            return redirect()->intended('/dashboard')->with('login_success', true);
        }

        RoleRedirector::clearUnsafeIntendedFor($user);

        return RoleRedirector::redirectFor($user)->with('login_success', true);
    }

    public function destroy(Request $request)
    {
        $this->service->logout($request);

        return redirect()->route('login')
            ->with('logout_success', true);
    }
}
