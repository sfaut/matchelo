{{-- Landing (Hi-Fi 1a / 1b) · desktop ≥ 900 px, composition du hero ≥ 1024 px --}}
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
    <header class="sticky top-0 z-10 border-b-2 border-ink bg-surface">
        <div class="mx-auto flex max-w-[1280px] items-center justify-between gap-3 px-[18px] py-2.5 desk:px-14 desk:py-[18px]">
            <a href="{{ route('home') }}" aria-label="Matchelo, accueil">
                <x-logo class="desk:[&_.mx-logo\_\_mark]:size-[46px] desk:[&_.mx-logo\_\_word]:text-[22px]" />
            </a>
            <nav class="hidden gap-8 text-[15px] lg:flex" aria-label="Sections">
                <a href="#fonctionnalites" class="no-underline">Fonctionnalités</a>
                <a href="#profs" class="no-underline">Pour les profs</a>
                <a href="#comment" class="no-underline">Comment ça marche</a>
            </nav>
            <div class="flex items-center gap-2.5 desk:gap-3">
                <x-theme-toggle />
                <a href="{{ route('login') }}" class="mx-btn mx-btn--sm px-3 desk:hidden">Connexion</a>
                <a href="{{ route('login') }}" class="mx-btn hidden desk:inline-flex">Se connecter</a>
                <a href="{{ route('register') }}" class="mx-btn mx-btn--primary hidden desk:inline-flex">Créer un compte</a>
            </div>
        </div>
    </header>

    <main>
        {{-- Hero --}}
        <section class="mx-auto grid max-w-[1280px] gap-[34px] px-5 pt-[30px] pb-[34px] desk:px-14 desk:pt-20 desk:pb-[76px] lg:grid-cols-[1.05fr_1fr] lg:items-center lg:gap-14">
            <div class="flex flex-col gap-[18px] desk:gap-6">
                <span class="inline-flex items-center gap-1.5 self-start -rotate-2 border-2 border-ink bg-highlight px-2.5 py-[5px] text-[13px] font-bold desk:px-3 desk:py-1.5 desk:text-sm">
                    <x-piece type="pawn" side="current" class="size-[1.15em]" />
                    <span class="desk:hidden">Pour les clubs scolaires</span>
                    <span class="hidden desk:inline">Pour les clubs d’échecs scolaires</span>
                </span>
                <h1 class="text-[46px] leading-[.96] font-bold tracking-[-.04em] text-balance desk:text-[78px]">Pointez, jouez, progressez.</h1>
                <p class="text-[17px] leading-[1.45] text-pretty text-muted desk:max-w-[520px] desk:text-xl">
                    <span class="desk:hidden">Présences, tournois, résultats : Matchelo organise le club. Chaque élève suit son ELO.</span>
                    <span class="hidden desk:inline">Matchelo organise votre club d’échecs : présences, tournois, résultats. Chaque élève suit son classement ELO, partie après partie.</span>
                </p>
                <div class="flex flex-col gap-3 desk:flex-row desk:gap-3.5">
                    <a href="{{ route('register') }}" class="mx-btn mx-btn--primary w-full p-3.5 text-base desk:w-auto desk:px-[22px] desk:text-[17px]">Créer mon club</a>
                    <a href="{{ route('register') }}" class="mx-btn w-full p-3.5 text-base desk:w-auto desk:px-[22px] desk:text-[17px]">J’ai un code d’invitation</a>
                </div>
                <ul class="mt-1.5 hidden gap-[22px] text-[15px] desk:flex">
                    @foreach (['Suisse, toutes rondes, élimination', 'ELO calculé automatiquement'] as $argument)
                        <li class="flex items-center gap-2">
                            <span class="grid size-5 place-items-center border-2 border-ink bg-highlight text-[13px] font-bold" aria-hidden="true">✓</span>
                            {{ $argument }}
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Illustration : échiquier et extraits de l'application --}}
            <div class="lg:relative lg:h-[500px]" aria-hidden="true">
                <div class="absolute top-0 right-0 hidden grid-cols-[repeat(6,62px)] border-2 border-ink lg:grid">
                    @for ($cell = 0; $cell < 36; $cell++)
                        <div @class([
                            'grid size-[62px] place-items-center',
                            'bg-(--mx-board-white)' => (intdiv($cell, 6) + $cell % 6) % 2 === 0,
                            'bg-(--mx-board-black)' => (intdiv($cell, 6) + $cell % 6) % 2 === 1,
                        ])>
                            @isset($board_pieces[$cell])
                                <x-piece :type="$board_pieces[$cell][0]" :side="$board_pieces[$cell][1]" class="size-[53px]" />
                            @endisset
                        </div>
                    @endfor
                </div>

                <div class="mx-card flex w-full max-w-[350px] flex-col gap-3 p-5 lg:absolute lg:top-14 lg:left-0 lg:w-[310px]">
                    <span class="mx-label">Mon classement</span>
                    <div class="flex items-baseline justify-between gap-2.5">
                        <span class="text-[52px] leading-none font-bold tracking-[-.03em] tabular-nums">1 248</span>
                        <span class="border-2 border-ink bg-surface px-2 py-[3px] text-sm font-bold">+36 ce mois</span>
                    </div>
                    <svg viewBox="0 0 220 60" preserveAspectRatio="none" class="block h-14 w-full">
                        <polyline points="0,52 22,48 44,50 66,38 88,42 110,30 132,33 154,22 176,26 198,12 220,8" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="miter" vector-effect="non-scaling-stroke" />
                    </svg>
                    <span class="text-sm text-muted">Léa Martin · 5e · 12 parties</span>
                </div>

                <div class="mx-card absolute top-[268px] right-6 hidden w-[330px] flex-col gap-3 p-5 lg:flex">
                    <span class="mx-label">Ronde 3 · échiquier 2</span>
                    <div class="flex flex-col gap-2 text-base">
                        <div class="flex items-center gap-2.5">
                            <span class="size-3.5 border-2 border-ink bg-(--mx-board-white)"></span>
                            <b class="flex-1">Tom Bernard</b>
                            <span class="text-muted tabular-nums">1 190</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="size-3.5 border-2 border-ink bg-(--mx-board-black)"></span>
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
                        <span class="text-[34px] leading-none font-bold tracking-[-.02em]">18/24</span>
                        <span class="text-sm">présents aujourd’hui</span>
                    </div>
                    <div class="grid grid-cols-[repeat(12,12px)] gap-1">
                        @for ($student = 0; $student < 24; $student++)
                            <span @class(['size-3 border-2 border-ink', 'bg-ink' => $student < 18, 'bg-surface' => $student >= 18])></span>
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        {{-- Frise d'échiquier --}}
        <div class="grid h-[18px] grid-cols-16 border-y-2 border-ink desk:h-[22px] desk:grid-cols-64" aria-hidden="true">
            @for ($square = 0; $square < 64; $square++)
                <div @class([
                    'bg-(--mx-board-black)' => $square % 2 === 0,
                    'bg-(--mx-board-white)' => $square % 2 === 1,
                    'max-desk:hidden' => $square >= 16,
                ])></div>
            @endfor
        </div>

        {{-- Pour les élèves --}}
        <section id="fonctionnalites" class="mx-auto flex max-w-[1280px] scroll-mt-20 flex-col gap-[18px] px-5 py-[34px] desk:gap-9 desk:px-14 desk:py-[84px]">
            <div class="flex flex-col gap-3">
                <span class="mx-label">Pour les élèves</span>
                <h2 class="text-[30px] leading-[1.05] font-bold tracking-[-.03em] text-balance desk:max-w-[720px] desk:text-[46px] desk:leading-[1.02]">Chaque partie fait bouger ton classement.</h2>
            </div>
            <div class="grid gap-[18px] desk:grid-cols-3 desk:gap-[22px]">
                @foreach ($student_features as [$piece, $title, $text])
                    <div class="flex items-start gap-3.5 desk:flex-col desk:gap-3 desk:border-2 desk:border-ink desk:bg-surface desk:p-6 desk:shadow-(--mx-sh)">
                        <span class="grid size-16 flex-none place-items-center border-2 border-ink bg-highlight desk:size-[76px]">
                            <x-piece :type="$piece" side="current" class="size-[41px] desk:size-12" />
                        </span>
                        <div class="flex flex-col gap-1 desk:gap-3">
                            <b class="text-[17px] desk:text-[21px] desk:tracking-[-.01em]">{{ $title }}</b>
                            <span class="text-sm leading-[1.4] text-muted desk:text-base desk:leading-[1.45]">{{ $text }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Pour les profs animateurs --}}
        <section id="profs" class="mx-inverse scroll-mt-20">
            <div class="mx-auto flex max-w-[1280px] flex-col gap-4 px-5 py-[34px] desk:gap-9 desk:px-14 desk:py-[84px]">
                <div class="flex flex-col gap-3">
                    <span class="mx-label text-surface-2">Pour les profs animateurs</span>
                    <h2 class="text-[30px] leading-[1.05] font-bold tracking-[-.03em] text-balance desk:text-[46px] desk:leading-[1.02]">Le prof anime, Matchelo organise.</h2>
                </div>
                <div class="grid gap-[22px] desk:grid-cols-2 lg:grid-cols-4">
                    <div class="mx-card mx-card--on-accent flex flex-col gap-3 p-[22px]">
                        <span class="grid size-[76px] place-items-center border-2 border-ink bg-highlight text-[40px] leading-none font-bold" aria-hidden="true">✓</span>
                        <b class="text-xl tracking-[-.01em]">Pointage en un geste</b>
                        <span class="text-[15px] leading-[1.45] text-muted">Cochez les élèves présents à chaque séance et repérez ceux qui décrochent.</span>
                    </div>
                    <div class="mx-card mx-card--on-accent flex flex-col gap-3 p-[22px]">
                        <span class="grid size-[76px] place-items-center border-2 border-ink bg-highlight">
                            <x-piece type="rook" side="current" class="size-12" />
                        </span>
                        <b class="text-xl tracking-[-.01em]">Tournois clés en main</b>
                        <span class="text-[15px] leading-[1.45] text-muted">Suisse, toutes rondes ou élimination directe : les appariements sont générés à chaque ronde.</span>
                    </div>
                    <div class="mx-card mx-card--on-accent flex flex-col gap-3 p-[22px]">
                        <span class="grid size-[76px] place-items-center border-2 border-ink bg-highlight text-[40px] leading-none font-bold" aria-hidden="true">½</span>
                        <b class="text-xl tracking-[-.01em]">Résultats en un tap</b>
                        <span class="text-[15px] leading-[1.45] text-muted">Un bouton par échiquier. Les classements se mettent à jour aussitôt.</span>
                    </div>
                    <div class="mx-card mx-card--on-accent hidden flex-col gap-3 p-[22px] desk:flex">
                        <span class="grid size-[76px] place-items-center border-2 border-ink bg-highlight text-[40px] leading-none font-bold" aria-hidden="true">#</span>
                        <b class="text-xl tracking-[-.01em]">Élèves et invitations</b>
                        <span class="text-[15px] leading-[1.45] text-muted">Ajoutez vos élèves ou partagez un code de club à saisir à l’inscription.</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Comment ça marche --}}
        <section id="comment" class="mx-auto hidden max-w-[1280px] scroll-mt-20 flex-col gap-9 px-14 py-[84px] desk:flex">
            <div class="flex flex-col gap-3">
                <span class="mx-label">Comment ça marche</span>
                <h2 class="text-[46px] leading-[1.02] font-bold tracking-[-.03em]">Prêt en une séance.</h2>
            </div>
            <ol class="grid grid-cols-3 gap-[22px]">
                @foreach ($steps as $index => [$title, $text])
                    <li class="flex items-start gap-[18px]">
                        <span class="grid size-16 flex-none place-items-center border-2 border-accent bg-accent text-[32px] font-bold text-on-accent shadow-(--mx-sh)">{{ $index + 1 }}</span>
                        <div class="flex flex-col gap-1.5 pt-1">
                            <b class="text-[21px]">{{ $title }}</b>
                            <span class="text-base leading-[1.45] text-muted">{{ $text }}</span>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Appel à l'action --}}
        <section class="mx-auto max-w-[1280px] px-5 py-[34px] desk:px-14 desk:pt-0 desk:pb-[84px]">
            <div class="mx-card flex flex-col gap-4 p-[22px] desk:flex-row desk:items-center desk:justify-between desk:gap-6 desk:p-10">
                <h2 class="text-[26px] leading-[1.05] font-bold tracking-[-.03em] desk:text-[40px]">Prêt pour la prochaine séance ?</h2>
                <div class="flex gap-3.5">
                    <a href="{{ route('register') }}" class="mx-btn mx-btn--primary w-full p-3.5 text-base desk:w-auto desk:px-[22px] desk:text-[17px]">Créer mon club</a>
                    <a href="{{ route('register') }}" class="mx-btn hidden px-[22px] py-3.5 text-[17px] desk:inline-flex">J’ai un code</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t-2 border-ink">
        <div class="mx-auto flex max-w-[1280px] items-center justify-between px-5 py-[18px] text-[13px] text-muted desk:px-14 desk:py-[22px] desk:text-sm">
            <x-logo class="hidden text-ink desk:inline-flex [&_.mx-logo\_\_mark]:size-[35px] [&_.mx-logo\_\_word]:text-[17px]" />
            <span class="desk:hidden">Mentions légales · Contact</span>
            <span class="hidden desk:inline">Mentions légales · Confidentialité · Contact</span>
        </div>
    </footer>
</x-layouts::app>
