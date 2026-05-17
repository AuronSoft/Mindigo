<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                
                // Nếu là guard admin → chuyển đến trang admin
                if ($guard === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                // Người dùng thường → chuyển đến dashboard
                return redirect()->route('dashboard'); 
                // Hoặc có thể dùng: return redirect()->intended(route('dashboard'));
            }
        }

        return $next($request);
    }
}