<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

class RoleRedirector
{
    public static function pathFor(?Authenticatable $user): string
    {
        return match ($user?->role ?? null) {
            'admin' => Route::has('dashboard') ? route('dashboard', [], false) : '/dashboard',
            'teacher' => Route::has('teacher.dashboard') ? route('teacher.dashboard', [], false) : '/teacher',
            'student' => Route::has('student.dashboard') ? route('student.dashboard', [], false) : '/student',
            default => Route::has('home') ? route('home', [], false) : '/',
        };
    }

    public static function redirectFor(?Authenticatable $user): RedirectResponse
    {
        $response = new RedirectResponse(self::pathFor($user));

        if (app()->bound('session.store')) {
            $response->setSession(session()->driver());
        }

        return $response;
    }

    public static function clearUnsafeIntendedFor(?Authenticatable $user): void
    {
        if (($user?->role ?? null) !== 'admin') {
            session()->forget('url.intended');
        }
    }
}
