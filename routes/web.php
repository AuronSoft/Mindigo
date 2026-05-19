<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;

Route::middleware('web')->get('/lang/{locale}', function ($locale, \Illuminate\Http\Request $request) {
    $supportedLocales = ['vi', 'en'];

    if (! in_array($locale, $supportedLocales, true)) {
        return redirect()->route('home');
    }

    $request->session()->put('locale', $locale);

    Cookie::queue('locale', $locale, 60 * 24 * 30);

    $previous = url()->previous();
    $previousHost = parse_url($previous, PHP_URL_HOST);
    $currentHost = $request->getHost();

    if (
        blank($previous)
        || ($previousHost && $previousHost !== $currentHost)
        || str_contains($previous, '/lang/')
    ) {
        return redirect()->route('home');
    }

    return redirect()->to($previous);
})->name('lang.switch');
