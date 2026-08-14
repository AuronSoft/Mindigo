<?php

namespace Mindigo\ExamManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExamBusinessRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role;
        abort_unless(in_array($role, ['teacher', 'student'], true), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
