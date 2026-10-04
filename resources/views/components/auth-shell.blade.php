@props(['panelTitle', 'panelText'])
{{-- Coque des pages d'authentification (Hi-Fi 1a) : panneau inversé avec échiquier à gauche sur desktop,
     en-tête compact (retour, logo, bascule) sur mobile, formulaire en carte sur desktop --}}
@php
    // Pièces de l'échiquier du panneau : index de case (0 à 63) => [type, camp]
    $board_pieces = [
        3 => ['king', 'black'],
        12 => ['bishop', 'black'],
        26 => ['knight', 'black'],
        37 => ['knight', 'white'],
        44 => ['pawn', 'white'],
        51 => ['pawn', 'white'],
        59 => ['king', 'white'],
    ];
@endphp
<div class="min-h-screen desk:grid desk:grid-cols-2">
    <aside class="mx-inverse hidden flex-col justify-between gap-10 px-14 py-11 desk:flex">
        <a href="{{ route('home') }}" class="self-start text-on-accent" aria-label="{{ __('landing.header.home_link') }}">
            <x-logo inverse class="mx-logo--lg" />
        </a>
        <div class="flex flex-col gap-7">
            <div class="grid w-max grid-cols-8 mx-border border-on-accent" aria-hidden="true">
                @for ($cell = 0; $cell < 64; $cell++)
                    <div @class([
                        'grid size-11.5 place-items-center',
                        'bg-board-white' => (intdiv($cell, 8) + $cell % 8) % 2 === 0,
                        'bg-board-black' => (intdiv($cell, 8) + $cell % 8) % 2 === 1,
                    ])>
                        @isset($board_pieces[$cell])
                            <x-piece :type="$board_pieces[$cell][0]" :side="$board_pieces[$cell][1]" class="size-9.5" />
                        @endisset
                    </div>
                @endfor
            </div>
            <p class="text-figure-lg tracking-display text-balance">{{ $panelTitle }}</p>
        </div>
        <p class="text-base">{{ $panelText }}</p>
    </aside>

    <div class="flex flex-col desk:relative desk:grid desk:place-items-center desk:p-10">
        <div class="flex items-center gap-3.5 px-4.5 pt-2 pb-1 desk:absolute desk:top-6 desk:right-7 desk:p-0">
            <a href="{{ route('home') }}" class="grid size-11 place-items-center mx-border bg-surface text-3xl font-bold desk:hidden" aria-label="{{ __('auth_pages.back_home') }}">←</a>
            <x-logo class="mx-logo--sm desk:hidden" />
            <div class="ml-auto">
                <x-scheme-toggle />
            </div>
        </div>
        <main class="w-full px-5 py-4 desk:mx-card desk:w-115 desk:p-8.5">
            {{ $slot }}
        </main>
    </div>
</div>
