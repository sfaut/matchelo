{{-- Écran provisoire des routes club.dashboard et player.home, remplacé aux étapes suivantes --}}
<x-layouts::app title="À venir">
    <main class="mx-auto flex max-w-xl flex-col items-start gap-6 px-4 py-14">
        <x-logo />
        <h1 class="text-[28px] leading-[1.1] font-bold tracking-[-.03em]">Écran à venir</h1>
        <a href="{{ route('home') }}" class="mx-btn">Retour à l’accueil</a>
    </main>
</x-layouts::app>
