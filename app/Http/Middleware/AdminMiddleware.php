<?php

namespace App\Http\Middleware;

use App\Support\RoleRedirector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $hasAdminRoleMethod = method_exists($user, 'hasRole') && $user->hasRole('admin');

        if (!$user->isAdmin() && !$hasAdminRoleMethod) {
            if ($request->expectsJson()) {
                abort(403, 'Bạn không có quyền truy cập khu vực quản trị.');
            }

            RoleRedirector::clearUnsafeIntendedFor($user);

            return RoleRedirector::redirectFor($user)
                ->with('warning', 'Bạn không có quyền truy cập khu vực quản trị.');
        }

        return $next($request);
    }
}
