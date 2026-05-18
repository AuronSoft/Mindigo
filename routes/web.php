<?php

use Illuminate\Support\Facades\Route;

Route::get('/lang/{locale}', function ($locale) {

    if (in_array($locale, ['vi', 'en'])) {
        session(['locale' => $locale]);
    }

    $previous = redirect()->getUrlGenerator()->previous();

    // Tránh redirect loop về chính /lang/
    if (str_contains($previous, '/lang/')) {
        return redirect('/');
    }

    return redirect($previous);

})->name('lang.switch');