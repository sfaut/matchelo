{{-- Landing (Hi-Fi 1a / 1b) · desktop ≥ 900 px (desk:), composition du hero ≥ 1024 px (lg:) --}}
@php
    $board_pieces = [
        1 => ['rook', 'black'],
        9 => ['knight', 'black'],
        20 => ['pawn', 'white'],
        27 => ['queen', 'white'],
        34 => ['knight', 'white'],
    ];
    // Pièce illustrant chaque atout élève, et préfixe de ses clefs dans lang/*/landing.php
    $student_features = [
        ['knight', 'elo'],
        ['rook', 'games'],
        ['queen', 'tournaments'],
    ];
    $steps = ['create', 'invite', 'tournament'];
@endphp
<x-layouts::app>
    <header class="sticky top-0 z-10 mx-border-b bg-surface">
        <div class="mx-auto flex max-w-page items-center justify-between gap-3 px-4.5 py-2.5 desk:px-14 desk:py-4.5">
            <a href="{{ route('home') }}" aria-label="{{ __('landing.header.home_link') }}">
                <x-logo class="desk:mx-logo--lg" />
            </a>
            <nav class="hidden gap-8 text-base lg:flex" aria-label="{{ __('landing.header.sections') }}">
                <a href="#features">{{ __('landing.header.features') }}</a>
                <a href="#teachers">{{ __('landing.header.teachers') }}</a>
                <a href="#how-it-works">{{ __('landing.header.how_it_works') }}</a>
            </nav>
            <div class="flex items-center gap-2.5 desk:gap-3">
                <x-scheme-toggle />
                <a href="{{ route('login') }}" class="mx-btn mx-btn--sm px-3 desk:hidden">{{ __('landing.header.log_in') }}</a>
                <a href="{{ route('login') }}" class="mx-btn hidden desk:inline-flex">{{ __('landing.header.log_in') }}</a>
                <a href="{{ route('register') }}" class="mx-btn mx-btn--primary hidden desk:inline-flex">{{ __('landing.header.sign_up') }}</a>
            </div>
        </div>
    </header>

    <main>
        {{-- Hero --}}
        <section class="mx-auto grid max-w-page gap-8.5 px-5 pt-7.5 pb-8.5 desk:px-14 desk:pt-20 desk:pb-19 lg:grid-cols-[1.05fr_1fr] lg:items-center lg:gap-14">
            <div class="flex flex-col gap-4.5 desk:gap-6">
                <span class="inline-flex items-center gap-1.5 self-start -rotate-2 mx-border bg-highlight px-2.5 py-1.25 text-sm font-bold desk:px-3 desk:py-1.5 desk:text-md">
                    <x-piece type="pawn" side="current" class="size-[1.15em]" />
                    <span class="desk:hidden">{{ __('landing.hero.badge_short') }}</span>
                    <span class="hidden desk:inline">{{ __('landing.hero.badge') }}</span>
                </span>
                <h1 class="text-h1 text-balance desk:text-h1-lg">{{ __('landing.hero.title') }}</h1>
                <p class="text-xl leading-relaxed text-pretty text-muted desk:max-w-130 desk:text-3xl">
                    <span class="desk:hidden">{{ __('landing.hero.lead_short') }}</span>
                    <span class="hidden desk:inline">{{ __('landing.hero.lead') }}</span>
                </p>
                <div class="flex flex-col gap-3 desk:flex-row desk:gap-3.5">
                    <a href="{{ route('register') }}" class="mx-btn mx-btn--primary w-full p-3.5 text-lg desk:w-auto desk:px-5.5 desk:text-xl">{{ __('landing.hero.create_club') }}</a>
                    <a href="{{ route('register') }}" class="mx-btn w-full p-3.5 text-lg desk:w-auto desk:px-5.5 desk:text-xl">{{ __('landing.hero.invite_code') }}</a>
                </div>
                <ul class="mt-1.5 hidden gap-5.5 text-base desk:flex">
                    @foreach ([__('landing.hero.formats'), __('landing.hero.automatic_elo')] as $argument)
                        <li class="flex items-center gap-2">
                            <span class="grid size-5 place-items-center mx-border bg-highlight text-sm font-bold" aria-hidden="true">✓</span>
                            {{ $argument }}
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Illustration : échiquier et extraits de l'application --}}
            <div class="lg:relative lg:h-125" aria-hidden="true">
                <div class="absolute top-0 right-0 hidden grid-cols-6 mx-border lg:grid">
                    @for ($cell = 0; $cell < 36; $cell++)
                        <div @class([
                            'grid size-15.5 place-items-center',
                            'bg-board-white' => (intdiv($cell, 6) + $cell % 6) % 2 === 0,
                            'bg-board-black' => (intdiv($cell, 6) + $cell % 6) % 2 === 1,
                        ])>
                            @isset($board_pieces[$cell])
                                <x-piece :type="$board_pieces[$cell][0]" :side="$board_pieces[$cell][1]" class="size-13.25" />
                            @endisset
                        </div>
                    @endfor
                </div>

                <div class="mx-card flex w-full max-w-87.5 flex-col gap-3 p-5 lg:absolute lg:top-14 lg:left-0 lg:w-77.5">
                    <span class="mx-label">{{ __('landing.illustration.my_rating') }}</span>
                    <div class="flex items-baseline justify-between gap-2.5">
                        <span class="text-figure-lg tabular-nums">{{ Number::format(1248) }}</span>
                        <span class="mx-border bg-surface px-2 py-0.75 text-md font-bold">{{ __('landing.illustration.rating_change', ['change' => '+'.Number::format(36)]) }}</span>
                    </div>
                    <svg viewBox="0 0 220 60" preserveAspectRatio="none" class="block h-14 w-full">
                        <polyline points="0,52 22,48 44,50 66,38 88,42 110,30 132,33 154,22 176,26 198,12 220,8" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="miter" vector-effect="non-scaling-stroke" />
                    </svg>
                    <span class="text-md text-muted">{{ __('landing.illustration.rating_meta', ['name' => 'Léa Martin', 'rank' => Number::ordinal(5), 'games' => trans_choice('landing.illustration.games', 12)]) }}</span>
                </div>

                <div class="mx-card absolute top-67 right-6 hidden w-82.5 flex-col gap-3 p-5 lg:flex">
                    <span class="mx-label">{{ __('landing.illustration.board', ['round' => 3, 'board' => 2]) }}</span>
                    <div class="flex flex-col gap-2 text-lg">
                        <div class="flex items-center gap-2.5">
                            <span class="size-3.5 mx-border bg-board-white"></span>
                            <b class="flex-1">Tom Bernard</b>
                            <span class="text-muted tabular-nums">{{ Number::format(1190) }}</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="size-3.5 mx-border bg-board-black"></span>
                            <b class="flex-1">Inès Kaci</b>
                            <span class="text-muted tabular-nums">{{ Number::format(1302) }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <span class="mx-btn p-2">1–0</span>
                        <span class="mx-btn mx-btn--primary p-2">½–½</span>
                        <span class="mx-btn p-2">0–1</span>
                    </div>
                </div>

                <div class="mx-card absolute bottom-0 left-9 hidden flex-col gap-2.5 px-4 py-3.5 lg:flex">
                    <div class="flex items-baseline gap-2">
                        <span class="text-figure">18/24</span>
                        <span class="text-md">{{ __('landing.illustration.present_today') }}</span>
                    </div>
                    <div class="grid w-max grid-cols-12 gap-1">
                        @for ($student = 0; $student < 24; $student++)
                            <span @class(['size-3 mx-border', 'bg-ink' => $student < 18, 'bg-surface' => $student >= 18])></span>
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        {{-- Frise d'échiquier --}}
        <div class="grid h-4.5 grid-cols-16 mx-border-y desk:h-5.5 desk:grid-cols-64" aria-hidden="true">
            @for ($square = 0; $square < 64; $square++)
                <div @class([
                    'bg-board-black' => $square % 2 === 0,
                    'bg-board-white' => $square % 2 === 1,
                    'max-desk:hidden' => $square >= 16,
                ])></div>
            @endfor
        </div>

        {{-- Pour les élèves --}}
        <section id="features" class="mx-auto flex max-w-page scroll-mt-20 flex-col gap-4.5 px-5 py-8.5 desk:gap-9 desk:px-14 desk:py-21">
            <div class="flex flex-col gap-3">
                <span class="mx-label">{{ __('landing.students.label') }}</span>
                <h2 class="text-h2 text-balance desk:max-w-180 desk:text-h2-lg">{{ __('landing.students.title') }}</h2>
            </div>
            <div class="grid gap-4.5 desk:grid-cols-3 desk:gap-5.5">
                @foreach ($student_features as [$piece, $feature])
                    <div class="flex items-start gap-3.5 desk:mx-card desk:flex-col desk:gap-3 desk:p-6">
                        <span class="grid size-16 flex-none place-items-center mx-border bg-highlight desk:size-19">
                            <x-piece :type="$piece" side="current" class="size-10.25 desk:size-12" />
                        </span>
                        <div class="flex flex-col gap-1 desk:gap-3">
                            <b class="text-xl desk:text-4xl desk:tracking-title">{{ __("landing.students.{$feature}_title") }}</b>
                            <span class="text-md leading-normal text-muted desk:text-lg desk:leading-relaxed">{{ __("landing.students.{$feature}_text") }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Pour les profs animateurs --}}
        <section id="teachers" class="mx-inverse scroll-mt-20">
            <div class="mx-auto flex max-w-page flex-col gap-4 px-5 py-8.5 desk:gap-9 desk:px-14 desk:py-21">
                <div class="flex flex-col gap-3">
                    <span class="mx-label text-surface-2">{{ __('landing.teachers.label') }}</span>
                    <h2 class="text-h2 text-balance desk:text-h2-lg">{{ __('landing.teachers.title') }}</h2>
                </div>
                <div class="grid gap-5.5 desk:grid-cols-2 lg:grid-cols-4">
                    <div class="mx-card mx-card--on-accent flex flex-col gap-3 p-5.5">
                        <span class="grid size-19 place-items-center mx-border bg-highlight text-icon" aria-hidden="true">✓</span>
                        <b class="text-3xl tracking-title">{{ __('landing.teachers.attendance_title') }}</b>
                        <span class="text-base leading-relaxed text-muted">{{ __('landing.teachers.attendance_text') }}</span>
                    </div>
                    <div class="mx-card mx-card--on-accent flex flex-col gap-3 p-5.5">
                        <span class="grid size-19 place-items-center mx-border bg-highlight">
                            <x-piece type="rook" side="current" class="size-12" />
                        </span>
                        <b class="text-3xl tracking-title">{{ __('landing.teachers.tournaments_title') }}</b>
                        <span class="text-base leading-relaxed text-muted">{{ __('landing.teachers.tournaments_text') }}</span>
                    </div>
                    <div class="mx-card mx-card--on-accent flex flex-col gap-3 p-5.5">
                        <span class="grid size-19 place-items-center mx-border bg-highlight text-icon" aria-hidden="true">½</span>
                        <b class="text-3xl tracking-title">{{ __('landing.teachers.results_title') }}</b>
                        <span class="text-base leading-relaxed text-muted">{{ __('landing.teachers.results_text') }}</span>
                    </div>
                    <div class="mx-card mx-card--on-accent hidden flex-col gap-3 p-5.5 desk:flex">
                        <span class="grid size-19 place-items-center mx-border bg-highlight text-icon" aria-hidden="true">#</span>
                        <b class="text-3xl tracking-title">{{ __('landing.teachers.students_title') }}</b>
                        <span class="text-base leading-relaxed text-muted">{{ __('landing.teachers.students_text') }}</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Comment ça marche --}}
        <section id="how-it-works" class="mx-auto hidden max-w-page scroll-mt-20 flex-col gap-9 px-14 py-21 desk:flex">
            <div class="flex flex-col gap-3">
                <span class="mx-label">{{ __('landing.how_it_works.label') }}</span>
                <h2 class="text-h2-lg">{{ __('landing.how_it_works.title') }}</h2>
            </div>
            <ol class="grid grid-cols-3 gap-5.5">
                @foreach ($steps as $index => $step)
                    <li class="flex items-start gap-4.5">
                        <span class="grid size-16 flex-none place-items-center mx-border border-accent bg-accent text-figure-sm text-on-accent shadow-solid">{{ $index + 1 }}</span>
                        <div class="flex flex-col gap-1.5 pt-1">
                            <b class="text-4xl">{{ __("landing.how_it_works.{$step}_title") }}</b>
                            <span class="text-lg leading-relaxed text-muted">{{ __("landing.how_it_works.{$step}_text") }}</span>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Appel à l'action --}}
        <section class="mx-auto max-w-page px-5 py-8.5 desk:px-14 desk:pt-0 desk:pb-21">
            <div class="mx-card flex flex-col gap-4 p-5.5 desk:flex-row desk:items-center desk:justify-between desk:gap-6 desk:p-10">
                <h2 class="text-h3 desk:text-h3-lg">{{ __('landing.cta.title') }}</h2>
                <div class="flex gap-3.5">
                    <a href="{{ route('register') }}" class="mx-btn mx-btn--primary w-full p-3.5 text-lg desk:w-auto desk:px-5.5 desk:text-xl">{{ __('landing.cta.create_club') }}</a>
                    <a href="{{ route('register') }}" class="mx-btn hidden px-5.5 py-3.5 text-xl desk:inline-flex">{{ __('landing.cta.code') }}</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="mx-border-t">
        <div class="mx-auto flex max-w-page items-center justify-between px-5 py-4.5 text-sm text-muted desk:px-14 desk:py-5.5 desk:text-md">
            <x-logo class="mx-logo--sm hidden text-ink desk:inline-flex" />
            <span class="desk:hidden">{{ __('landing.footer.links_short') }}</span>
            <span class="hidden desk:inline">{{ __('landing.footer.links') }}</span>
        </div>
    </footer>
</x-layouts::app>
