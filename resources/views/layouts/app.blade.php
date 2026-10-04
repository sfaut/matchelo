<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-scheme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ isset($title) ? "{$title} · " : '' }}{{ config('app.name', 'Matchelo') }}</title>

        {{-- Thème mémorisé appliqué avant le premier rendu, pour éviter le flash clair / sombre --}}
        <script>
            try {
                document.documentElement.dataset.scheme = localStorage.getItem('matchelo-scheme') || 'light';
            } catch {}
        </script>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        {{-- Thème de l'utilisateur (variables CSS), chargé après app.css --}}
        @inject('themes', \App\Support\Themes::class)
        @vite(['resources/css/app.css', $themes->stylesheet(), 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-bg text-ink antialiased">
        {{ $slot }}

        {{-- Livewire et Alpine viennent du bundle Vite (resources/js/app.js) : seule leur configuration est injectée --}}
        @livewireScriptConfig
    </body>
</html>
