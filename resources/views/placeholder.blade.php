{{-- Écran provisoire des routes à venir (connexion, inscription, club, jeu), remplacé aux étapes suivantes --}}
<x-layouts::app title="À venir">
    <main class="mx-auto flex max-w-144 flex-col items-start gap-6 px-4 py-14">
        <x-logo />
        <h1 class="text-title">Écran à venir</h1>
        <a href="{{ route('home') }}" class="mx-btn">Retour à l’accueil</a>
    </main>
</x-layouts::app>
