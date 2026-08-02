<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" translate="no">
<head>
    <meta charset="UTF-8" />
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', $title ?? config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', __('core::app.meta.description'))">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title', $title ?? config('app.name'))))">
    <meta property="og:description" content="@yield('og_description', trim($__env->yieldContent('meta_description', __('core::app.meta.description'))) )">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:type" content="website">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        font { display: contents !important; }
    </style>
</head>
<body class="bg-white text-gray-800">
    @yield('content')
</body>
</html>
