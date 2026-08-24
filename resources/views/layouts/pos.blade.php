<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'PDV').' | Sistema de Cantina escolar' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .pos-touch { -webkit-tap-highlight-color: transparent; touch-action: manipulation; }
        .pos-scroll { -webkit-overflow-scrolling: touch; }
    </style>
</head>
<body class="h-full overflow-hidden bg-slate-100 text-slate-900 antialiased pos-touch">
    @yield('content')
    @stack('scripts')
</body>
</html>
