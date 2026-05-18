<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" translate="no">
<head>
    <meta charset="UTF-8" />
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? config('app.name') }}</title>
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