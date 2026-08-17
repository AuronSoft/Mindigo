<?php

namespace Mindigo\Auth\Http\Controllers;

use App\Support\Auth\RoleRedirector;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\Auth\Http\Requests\LoginRequest;
use Mindigo\Auth\Services\LoginService;

class LoginController extends Controller
{
    public function __construct(
        private readonly LoginService $service
    ) {}

    public function index(Request $request)
    {
        RoleRedirector::rememberSafeIntendedFrom($request);

        return view('Mindigo-auth::login');
    }

    public function store(LoginRequest $request)
    {
        $this->service->checkThrottle($request);
        $this->service->attempt($request);
        $this->service->persistSession($request);

        /** @var Authenticatable $user */
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Unauthenticated after login attempt.');
        }

        $response = $user->role === 'admin'
            ? redirect()->intended('/dashboard')
            : RoleRedirector::redirectAfterLoginFor($user);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Mindigo-auth::app.processing.success'),
                'redirect' => $response->getTargetUrl(),
            ]);
        }

        return $response->with('login_success', true);
    }

    public function destroy(Request $request)
    {
        $this->service->logout($request);

        return redirect()->route('login')
            ->with('logout_success', true);
    }
}
