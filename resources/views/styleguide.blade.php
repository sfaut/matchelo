{{-- Planche de référence des fondations visuelles du thème courant (modes clair / sombre), hors production --}}
@php
    $piece_types = ['king', 'queen', 'rook', 'bishop', 'knight', 'pawn'];
    // Classes écrites en entier : Tailwind ne détecte pas les noms construits dynamiquement
    $colors = [
        'bg' => 'bg-bg',
        'surface' => 'bg-surface',
        'surface-2' => 'bg-surface-2',
        'ink' => 'bg-ink',
        'muted' => 'bg-muted',
        'accent' => 'bg-accent',
        'on-accent' => 'bg-on-accent',
        'highlight' => 'bg-highlight',
        'error' => 'bg-error',
        'board-white' => 'bg-board-white',
        'board-black' => 'bg-board-black',
    ];
    $text_sizes = [
        'text-xs', 'text-sm', 'text-md', 'text-base', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl', 'text-4xl', 'text-5xl',
        'text-title', 'text-h3', 'text-h3-lg', 'text-h2', 'text-h2-lg', 'text-h1', 'text-h1-lg',
        'text-figure-sm', 'text-figure', 'text-figure-lg', 'text-icon',
    ];
    $strokes = ['mx-border', 'mx-border-t', 'mx-border-r', 'mx-border-b', 'mx-border-y'];
@endphp
<x-layouts::app :title="__('styleguide.title')">
    <header class="flex items-center justify-between gap-4 mx-border-b bg-surface px-4 py-3 md:px-8">
        <x-logo />
        <x-scheme-toggle />
    </header>

    <main class="mx-auto flex max-w-256 flex-col gap-14 px-4 py-10 md:px-8">
        <h1 class="text-h1">{{ __('styleguide.title') }}</h1>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.colors') }}</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 md:grid-cols-6">
                @foreach ($colors as $color => $color_class)
                    <div class="flex flex-col gap-1.5">
                        <span @class(['h-12 mx-border', $color_class])></span>
                        <code class="text-sm">{{ $color }}</code>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.typography') }}</h2>
            <div class="flex flex-col gap-3">
                @foreach ($text_sizes as $text_size)
                    <div class="flex flex-col gap-1 mx-border-b pb-3">
                        <code class="text-xs text-muted">{{ $text_size }}</code>
                        <p @class([$text_size])>{{ __('styleguide.typography_sample') }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.strokes_shadows') }}</h2>
            <div class="flex flex-wrap gap-6">
                @foreach ($strokes as $stroke)
                    <div class="flex flex-col gap-1.5">
                        <span @class(['size-16 bg-surface', $stroke])></span>
                        <code class="text-sm">{{ $stroke }}</code>
                    </div>
                @endforeach
                <div class="flex flex-col gap-1.5">
                    <span class="size-16 mx-border bg-surface shadow-solid"></span>
                    <code class="text-sm">shadow-solid</code>
                </div>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.buttons') }}</h2>
            <div class="flex flex-wrap items-center gap-6">
                <button type="button" class="mx-btn mx-btn--primary">{{ __('styleguide.take_attendance') }}</button>
                <button type="button" class="mx-btn">{{ __('styleguide.enter_results') }}</button>
                <button type="button" class="mx-btn mx-btn--sm">{{ __('styleguide.new_tournament') }}</button>
                <button type="button" class="mx-btn mx-btn--link">{{ __('styleguide.reset') }}</button>
                <button type="button" class="mx-btn" disabled>{{ __('styleguide.disabled') }}</button>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.cards') }}</h2>
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="mx-card">
                    <p class="text-sm text-muted">{{ __('styleguide.my_elo') }}</p>
                    <p class="text-figure-sm">{{ Number::format(1248) }}</p>
                </div>
                <div class="mx-card mx-card--highlight">
                    <p class="text-sm text-muted">{{ __('styleguide.highlighted_card') }}</p>
                    <p class="text-figure-sm">{{ Number::format(1312) }}</p>
                </div>
                <div class="mx-inverse p-6 sm:col-span-2">
                    <div class="mx-card mx-card--on-accent">
                        <p class="text-sm text-muted">{{ __('styleguide.card_on_accent') }}</p>
                        <p class="text-figure-sm">{{ Number::format(1190) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.fields') }}</h2>
            <div class="grid gap-6 sm:grid-cols-3">
                <label class="flex flex-col gap-2">
                    <span class="mx-label">{{ __('styleguide.idle') }}</span>
                    <input type="email" class="mx-input" placeholder="{{ __('styleguide.email_placeholder') }}">
                </label>
                <label class="flex flex-col gap-2">
                    <span class="mx-label">{{ __('styleguide.focus') }}</span>
                    {{-- Ombre de :focus reproduite pour la planche ; au repos, un champ n'a pas d'ombre --}}
                    <input type="text" class="mx-input shadow-solid" value="JM-4821">
                </label>
                <label class="flex flex-col gap-2">
                    <span class="mx-label">{{ __('styleguide.error') }}</span>
                    <input type="password" class="mx-input" value="abc" aria-invalid="true" aria-describedby="styleguide-error">
                    <span id="styleguide-error" class="mx-error">{{ __('styleguide.password_error') }}</span>
                </label>
                <div class="flex flex-col gap-2">
                    <label for="styleguide-civility" class="mx-label">{{ __('styleguide.select') }}</label>
                    <select id="styleguide-civility" class="mx-input">
                        <option>{{ __('civilities.MADAM') }}</option>
                        <option>{{ __('civilities.MISTER') }}</option>
                    </select>
                </div>
                <div class="flex flex-col gap-2 sm:col-span-2">
                    <label for="styleguide-password" class="mx-label">{{ __('styleguide.password') }}</label>
                    <x-password-input id="styleguide-password" value="echecs-2026" />
                </div>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.badge') }}</h2>
            <div class="flex flex-wrap items-center gap-4">
                <span class="mx-badge">{{ __('clubs.index.animator_badge') }}</span>
                <span class="mx-badge">{{ __('components.app_shell.animator_badge') }}</span>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.divider') }}</h2>
            <div class="mx-divider max-w-115">{{ __('auth_pages.login.or') }}</div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.chips') }}</h2>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="mx-chip" aria-pressed="true">{{ __('styleguide.mine') }}</button>
                <button type="button" class="mx-chip" aria-pressed="false">{{ __('styleguide.whole_club') }}</button>
                <button type="button" class="mx-chip" aria-pressed="false">{{ __('styleguide.in_progress') }}</button>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.segmented_control') }}</h2>
            <div class="mx-seg" role="group" aria-label="{{ __('styleguide.profile') }}">
                <button type="button" class="mx-seg__item" aria-pressed="true">{{ __('styleguide.student') }}</button>
                <button type="button" class="mx-seg__item" aria-pressed="false">{{ __('styleguide.teacher') }}</button>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.mode_switch') }}</h2>
            <div class="flex flex-wrap gap-6">
                <x-mode-switch mode="manage" />
                <x-mode-switch mode="play" />
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.scheme_toggle') }}</h2>
            <x-scheme-toggle />
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.logo') }}</h2>
            <div class="flex flex-wrap items-stretch gap-6">
                <div class="flex items-center gap-6 p-6">
                    <x-logo class="mx-logo--sm" />
                    <x-logo />
                    <x-logo class="mx-logo--lg" />
                </div>
                <div class="mx-inverse flex items-center p-6">
                    <x-logo inverse />
                </div>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.toast') }}</h2>
            <div class="flex">
                <div class="mx-toast" role="status">{{ __('styleguide.invite_link_copied') }}</div>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">{{ __('styleguide.pieces') }}</h2>
            @foreach (['white', 'black'] as $side)
                <div class="flex w-max mx-border bg-board-white">
                    @foreach ($piece_types as $type)
                        <span class="grid size-10 place-items-center">
                            <x-piece :type="$type" :side="$side" />
                        </span>
                    @endforeach
                </div>
            @endforeach
            <div class="flex w-max mx-border bg-board-black">
                @foreach (['white', 'black'] as $side)
                    @foreach ($piece_types as $type)
                        <span class="grid size-10 place-items-center">
                            <x-piece :type="$type" :side="$side" />
                        </span>
                    @endforeach
                @endforeach
            </div>
            <div class="flex w-max gap-1 text-ink">
                @foreach ($piece_types as $type)
                    <x-piece :type="$type" side="current" />
                @endforeach
            </div>
            <p class="text-sm text-muted">{!! __('styleguide.piece_files', ['path' => '<code>public/images/pieces</code>']) !!}</p>
            <div class="flex w-max mx-border bg-board-white">
                @foreach (['white', 'black'] as $side)
                    @foreach ($piece_types as $type)
                        <span class="grid size-10 place-items-center">
                            <img src="{{ asset("images/pieces/{$side}-{$type}.svg") }}" alt="" class="size-8">
                        </span>
                    @endforeach
                @endforeach
            </div>
        </section>
    </main>
</x-layouts::app>
