{{-- Écran provisoire des routes à venir (connexion, inscription, club, jeu), remplacé aux étapes suivantes --}}
<x-layouts::app :title="__('placeholder.title')">
    <main class="mx-auto flex max-w-144 flex-col items-start gap-6 px-4 py-14">
        <x-logo />
        <h1 class="text-title">{{ __('placeholder.heading') }}</h1>
        <a href="{{ route('home') }}" class="mx-btn">{{ __('placeholder.back_home') }}</a>
    </main>
</x-layouts::app>
