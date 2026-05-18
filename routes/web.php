<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->get('/lang/{locale}', function ($locale, \Illuminate\Http\Request $request) {
    if (in_array($locale, ['vi', 'en'])) {
        $request->session()->put('locale', $locale);
    }

    $previous = redirect()->getUrlGenerator()->previous();

    if (str_contains($previous, '/lang/')) {
        return redirect('/');
    }

    return redirect($previous);
})->name('lang.switch');