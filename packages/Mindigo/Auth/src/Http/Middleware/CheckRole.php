<?php

namespace Mindigo\Auth\Http\Middleware;

use App\Support\RoleRedirector;
use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowed = [];

        foreach ($roles as $role) {
            foreach (explode('|', $role) as $item) {
                $allowed[] = trim($item);
            }
        }

        if (! in_array($user->role, $allowed, true)) {
            if ($request->expectsJson() || $allowed === ['admin']) {
                abort(403);
            }

            RoleRedirector::clearUnsafeIntendedFor($user);

            return RoleRedirector::redirectFor($user)
                ->with('warning', __('Mindigo-auth::app.auth.no_permission'));
        }

        return $next($request);
    }
}
