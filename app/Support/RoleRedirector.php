<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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

    public static function redirectAfterLoginFor(?Authenticatable $user): RedirectResponse
    {
        self::clearUnsafeIntendedFor($user);

        return redirect()->intended(self::pathFor($user));
    }

    public static function rememberSafeIntendedFrom(Request $request): void
    {
        $redirect = $request->query('redirect');

        if (! is_string($redirect)) {
            return;
        }

        $safe = self::safeIntendedUrl($redirect);

        if ($safe) {
            session()->put('url.intended', $safe);
        }
    }

    public static function clearUnsafeIntendedFor(?Authenticatable $user): void
    {
        if (($user?->role ?? null) !== 'admin' && ! self::safeIntendedUrl(session('url.intended'))) {
            session()->forget('url.intended');
        }
    }

    public static function safeIntendedUrl(mixed $url): ?string
    {
        if (! is_string($url) || $url === '' || Str::startsWith($url, ['//', '\\\\'])) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        if ($path === (Route::has('exam-tips') ? route('exam-tips', [], false) : '/exam-tips')) {
            return $path;
        }

        return null;
    }
}
