<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ isset($title) ? "{$title} · " : '' }}{{ config('app.name', 'Matchelo') }}</title>

        {{-- Thème mémorisé appliqué avant le premier rendu, pour éviter le flash clair / sombre --}}
        <script>
            try {
                document.documentElement.dataset.theme = localStorage.getItem('matchelo-theme') || 'light';
            } catch {}
        </script>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-bg text-ink antialiased">
        {{ $slot }}
    </body>
</html>
