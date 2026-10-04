{{-- Planche de référence des fondations visuelles du thème courant (modes clair / sombre), hors production --}}
@php
    $piece_types = ['king', 'queen', 'rook', 'bishop', 'knight', 'pawn'];
@endphp
<x-layouts::app :title="__('styleguide.title')">
    <header class="flex items-center justify-between gap-4 mx-border-b bg-surface px-4 py-3 md:px-8">
        <x-logo />
        <x-scheme-toggle />
    </header>

    <main class="mx-auto flex max-w-256 flex-col gap-14 px-4 py-10 md:px-8">
        <h1 class="text-h1">{{ __('styleguide.title') }}</h1>

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
            </div>
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
                <div class="flex items-center p-6">
                    <x-logo />
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
