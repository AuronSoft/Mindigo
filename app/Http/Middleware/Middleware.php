<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    abstract public function handle(Request $request, Closure $next): Response;

    /**
     * Helper method: Kiểm tra user đã đăng nhập chưa
     */
    protected function isAuthenticated(Request $request): bool
    {
        return $request->user() !== null;
    }

    /**
     * Helper method: Kiểm tra user có role/permission nào đó
     */
    protected function hasPermission(Request $request, string $permission): bool
    {
        $user = $request->user();
        return $user && method_exists($user, 'hasPermissionTo')
            ? $user->hasPermissionTo($permission)
            : false;
    }

    /**
     * Helper method: Kiểm tra user có role
     */
    protected function hasRole(Request $request, string|array $roles): bool
    {
        $user = $request->user();
        if (!$user) return false;

        return method_exists($user, 'hasRole')
            ? $user->hasRole($roles)
            : in_array($user->role ?? '', (array)$roles);
    }

    /**
     * Response JSON khi không có quyền
     */
    protected function forbiddenResponse(string $message = 'Bạn không có quyền truy cập.')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }

    /**
     * Response JSON khi chưa đăng nhập
     */
    protected function unauthorizedResponse(string $message = 'Vui lòng đăng nhập để tiếp tục.')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }
}