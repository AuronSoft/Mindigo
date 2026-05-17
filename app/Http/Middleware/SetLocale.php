<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->getPreferredLanguage(['vi', 'en']) ?? 'vi';
        
        // Ưu tiên session trước, sau đó là request
        if ($request->has('lang')) {
            $locale = $request->get('lang');
        } elseif ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }

        // Chỉ chấp nhận vi và en
        $locale = in_array($locale, ['vi', 'en']) ? $locale : 'vi';

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}