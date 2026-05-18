<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        font { display: contents !important; }
    </style>
</head>
<body class="bg-white text-gray-800">
    @yield('content')
</body>
</html>