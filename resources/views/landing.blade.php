{{-- Landing (Hi-Fi 1a / 1b) · desktop ≥ 900 px (desk:), composition du hero ≥ 1024 px (lg:) --}}
@php
    $board_pieces = [
        1 => ['rook', 'black'],
        9 => ['knight', 'black'],
        20 => ['pawn', 'white'],
        27 => ['queen', 'white'],
        34 => ['knight', 'white'],
    ];
    $student_features = [
        ['knight', 'Ta courbe ELO', 'Ton classement évolue après chaque partie saisie. Repère tes progrès, ton record et ta forme du moment.'],
        ['rook', 'Tes parties', 'Résultat, couleur, adversaire et points gagnés ou perdus, pour chaque rencontre.'],
        ['queen', 'Tes tournois', 'Classement final, ronde par ronde, et ta place dans chaque tournoi joué.'],
    ];
    $steps = [
        ['Créez votre club', 'Nom, établissement, jour de séance. Vous en êtes l’animateur.'],
        ['Invitez les élèves', 'Ils s’inscrivent avec le code du club, vous validez.'],
        ['Lancez un tournoi', 'Choisissez le format et les présents, Matchelo apparie la ronde 1.'],
    ];
@endphp
<x-layouts::app>
    <header class="sticky top-0 z-10 mx-border-b bg-surface">
        <div class="mx-auto flex max-w-page items-center justify-between gap-3 px-4.5 py-2.5 desk:px-14 desk:py-4.5">
            <a href="{{ route('home') }}" aria-label="Matchelo, accueil">
                <x-logo class="desk:mx-logo--lg" />
            </a>
            <nav class="hidden gap-8 text-base lg:flex" aria-label="Sections">
                <a href="#features">Fonctionnalités</a>
                <a href="#teachers">Pour les profs</a>
                <a href="#how-it-works">Comment ça marche</a>
            </nav>
            <div class="flex items-center gap-2.5 desk:gap-3">
                <x-scheme-toggle />
                <a href="{{ route('login') }}" class="mx-btn mx-btn--sm px-3 desk:hidden">Connexion</a>
                <a href="{{ route('login') }}" class="mx-btn hidden desk:inline-flex">Se connecter</a>
                <a href="{{ route('register') }}" class="mx-btn mx-btn--primary hidden desk:inline-flex">Créer un compte</a>
            </div>
        </div>
    </header>

    <main>
        {{-- Hero --}}
        <section class="mx-auto grid max-w-page gap-8.5 px-5 pt-7.5 pb-8.5 desk:px-14 desk:pt-20 desk:pb-19 lg:grid-cols-[1.05fr_1fr] lg:items-center lg:gap-14">
            <div class="flex flex-col gap-4.5 desk:gap-6">
                <span class="inline-flex items-center gap-1.5 self-start -rotate-2 mx-border bg-highlight px-2.5 py-1.25 text-sm font-bold desk:px-3 desk:py-1.5 desk:text-md">
                    <x-piece type="pawn" side="current" class="size-[1.15em]" />
                    <span class="desk:hidden">Pour les clubs scolaires</span>
                    <span class="hidden desk:inline">Pour les clubs d’échecs scolaires</span>
                </span>
                <h1 class="text-h1 text-balance desk:text-h1-lg">Pointez, jouez, progressez.</h1>
                <p class="text-xl leading-relaxed text-pretty text-muted desk:max-w-130 desk:text-3xl">
                    <span class="desk:hidden">Présences, tournois, résultats : Matchelo organise le club. Chaque élève suit son ELO.</span>
                    <span class="hidden desk:inline">Matchelo organise votre club d’échecs : présences, tournois, résultats. Chaque élève suit son classement ELO, partie après partie.</span>
                </p>
                <div class="flex flex-col gap-3 desk:flex-row desk:gap-3.5">
                    <a href="{{ route('register') }}" class="mx-btn mx-btn--primary w-full p-3.5 text-lg desk:w-auto desk:px-5.5 desk:text-xl">Créer mon club</a>
                    <a href="{{ route('register') }}" class="mx-btn w-full p-3.5 text-lg desk:w-auto desk:px-5.5 desk:text-xl">J’ai un code d’invitation</a>
                </div>
                <ul class="mt-1.5 hidden gap-5.5 text-base desk:flex">
                    @foreach (['Suisse, toutes rondes, élimination', 'ELO calculé automatiquement'] as $argument)
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
                    <span class="mx-label">Mon classement</span>
                    <div class="flex items-baseline justify-between gap-2.5">
                        <span class="text-figure-lg tabular-nums">1 248</span>
                        <span class="mx-border bg-surface px-2 py-0.75 text-md font-bold">+36 ce mois</span>
                    </div>
                    <svg viewBox="0 0 220 60" preserveAspectRatio="none" class="block h-14 w-full">
                        <polyline points="0,52 22,48 44,50 66,38 88,42 110,30 132,33 154,22 176,26 198,12 220,8" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="miter" vector-effect="non-scaling-stroke" />
                    </svg>
                    <span class="text-md text-muted">Léa Martin · 5e · 12 parties</span>
                </div>

                <div class="mx-card absolute top-67 right-6 hidden w-82.5 flex-col gap-3 p-5 lg:flex">
                    <span class="mx-label">Ronde 3 · échiquier 2</span>
                    <div class="flex flex-col gap-2 text-lg">
                        <div class="flex items-center gap-2.5">
                            <span class="size-3.5 mx-border bg-board-white"></span>
                            <b class="flex-1">Tom Bernard</b>
                            <span class="text-muted tabular-nums">1 190</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="size-3.5 mx-border bg-board-black"></span>
                            <b class="flex-1">Inès Kaci</b>
                            <span class="text-muted tabular-nums">1 302</span>
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
                        <span class="text-md">présents aujourd’hui</span>
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
                <span class="mx-label">Pour les élèves</span>
                <h2 class="text-h2 text-balance desk:max-w-180 desk:text-h2-lg">Chaque partie fait bouger ton classement.</h2>
            </div>
            <div class="grid gap-4.5 desk:grid-cols-3 desk:gap-5.5">
                @foreach ($student_features as [$piece, $title, $text])
                    <div class="flex items-start gap-3.5 desk:mx-card desk:flex-col desk:gap-3 desk:p-6">
                        <span class="grid size-16 flex-none place-items-center mx-border bg-highlight desk:size-19">
                            <x-piece :type="$piece" side="current" class="size-10.25 desk:size-12" />
                        </span>
                        <div class="flex flex-col gap-1 desk:gap-3">
                            <b class="text-xl desk:text-4xl desk:tracking-title">{{ $title }}</b>
                            <span class="text-md leading-normal text-muted desk:text-lg desk:leading-relaxed">{{ $text }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Pour les profs animateurs --}}
        <section id="teachers" class="mx-inverse scroll-mt-20">
            <div class="mx-auto flex max-w-page flex-col gap-4 px-5 py-8.5 desk:gap-9 desk:px-14 desk:py-21">
                <div class="flex flex-col gap-3">
                    <span class="mx-label text-surface-2">Pour les profs animateurs</span>
                    <h2 class="text-h2 text-balance desk:text-h2-lg">Le prof anime, Matchelo organise.</h2>
                </div>
                <div class="grid gap-5.5 desk:grid-cols-2 lg:grid-cols-4">
                    <div class="mx-card mx-card--on-accent flex flex-col gap-3 p-5.5">
                        <span class="grid size-19 place-items-center mx-border bg-highlight text-icon" aria-hidden="true">✓</span>
                        <b class="text-3xl tracking-title">Pointage en un geste</b>
                        <span class="text-base leading-relaxed text-muted">Cochez les élèves présents à chaque séance et repérez ceux qui décrochent.</span>
                    </div>
                    <div class="mx-card mx-card--on-accent flex flex-col gap-3 p-5.5">
                        <span class="grid size-19 place-items-center mx-border bg-highlight">
                            <x-piece type="rook" side="current" class="size-12" />
                        </span>
                        <b class="text-3xl tracking-title">Tournois clés en main</b>
                        <span class="text-base leading-relaxed text-muted">Suisse, toutes rondes ou élimination directe : les appariements sont générés à chaque ronde.</span>
                    </div>
                    <div class="mx-card mx-card--on-accent flex flex-col gap-3 p-5.5">
                        <span class="grid size-19 place-items-center mx-border bg-highlight text-icon" aria-hidden="true">½</span>
                        <b class="text-3xl tracking-title">Résultats en un tap</b>
                        <span class="text-base leading-relaxed text-muted">Un bouton par échiquier. Les classements se mettent à jour aussitôt.</span>
                    </div>
                    <div class="mx-card mx-card--on-accent hidden flex-col gap-3 p-5.5 desk:flex">
                        <span class="grid size-19 place-items-center mx-border bg-highlight text-icon" aria-hidden="true">#</span>
                        <b class="text-3xl tracking-title">Élèves et invitations</b>
                        <span class="text-base leading-relaxed text-muted">Ajoutez vos élèves ou partagez un code de club à saisir à l’inscription.</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Comment ça marche --}}
        <section id="how-it-works" class="mx-auto hidden max-w-page scroll-mt-20 flex-col gap-9 px-14 py-21 desk:flex">
            <div class="flex flex-col gap-3">
                <span class="mx-label">Comment ça marche</span>
                <h2 class="text-h2-lg">Prêt en une séance.</h2>
            </div>
            <ol class="grid grid-cols-3 gap-5.5">
                @foreach ($steps as $index => [$title, $text])
                    <li class="flex items-start gap-4.5">
                        <span class="grid size-16 flex-none place-items-center mx-border border-accent bg-accent text-figure-sm text-on-accent shadow-solid">{{ $index + 1 }}</span>
                        <div class="flex flex-col gap-1.5 pt-1">
                            <b class="text-4xl">{{ $title }}</b>
                            <span class="text-lg leading-relaxed text-muted">{{ $text }}</span>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Appel à l'action --}}
        <section class="mx-auto max-w-page px-5 py-8.5 desk:px-14 desk:pt-0 desk:pb-21">
            <div class="mx-card flex flex-col gap-4 p-5.5 desk:flex-row desk:items-center desk:justify-between desk:gap-6 desk:p-10">
                <h2 class="text-h3 desk:text-h3-lg">Prêt pour la prochaine séance ?</h2>
                <div class="flex gap-3.5">
                    <a href="{{ route('register') }}" class="mx-btn mx-btn--primary w-full p-3.5 text-lg desk:w-auto desk:px-5.5 desk:text-xl">Créer mon club</a>
                    <a href="{{ route('register') }}" class="mx-btn hidden px-5.5 py-3.5 text-xl desk:inline-flex">J’ai un code</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="mx-border-t">
        <div class="mx-auto flex max-w-page items-center justify-between px-5 py-4.5 text-sm text-muted desk:px-14 desk:py-5.5 desk:text-md">
            <x-logo class="mx-logo--sm hidden text-ink desk:inline-flex" />
            <span class="desk:hidden">Mentions légales · Contact</span>
            <span class="hidden desk:inline">Mentions légales · Confidentialité · Contact</span>
        </div>
    </footer>
</x-layouts::app>
