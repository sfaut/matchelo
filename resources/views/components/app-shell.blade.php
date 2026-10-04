{{-- Coque de l'application connectée (App\View\Components\AppShell) --}}
<div class="flex min-h-screen flex-col">
    <header class="flex flex-none flex-wrap items-center justify-between gap-3 mx-border-b bg-surface px-4.5 py-3.5 desk:h-16 desk:flex-nowrap desk:py-0">
        <div class="flex min-w-0 items-center gap-5.5">
            {{-- En gestion, le sélecteur de club remplace le logo sur mobile --}}
            <a href="{{ route('clubs.index') }}" @class(['hidden desk:block' => $isManaging()]) aria-label="{{ __('components.app_shell.home_link') }}">
                <x-logo />
            </a>

            @if ($isManaging())
                {{-- Sélecteur des clubs animés --}}
                <div class="relative min-w-0" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape="open = false">
                    <button type="button" class="mx-btn mx-btn--sm max-w-full" aria-haspopup="true" aria-expanded="false" x-bind:aria-expanded="open" x-on:click="open = ! open">
                        <span class="truncate">{{ $club->name }}</span> ▾
                    </button>
                    <div class="mx-card absolute top-full left-0 z-30 mt-2 flex min-w-64 flex-col p-1.5" x-show="open" x-cloak>
                        @foreach ($animatedClubs as $animated_club)
                            <a href="{{ route('clubs.dashboard', $animated_club) }}" class="mx-nav-item" @if ($animated_club->is($club)) aria-current="page" @endif wire:key="managed-club-{{ $animated_club->id }}">
                                {{ $animated_club->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-2.5">
            @if ($isAnimator())
                <x-mode-switch :mode="$mode" class="hidden desk:grid" />
            @endif
            <x-scheme-toggle />
            <span class="grid size-8.5 place-items-center mx-border bg-highlight text-xs font-bold" title="{{ __('components.app_shell.account', ['name' => $user->fullName()]) }}">
                {{ $user->initials() }}
            </span>
        </div>

        @if ($isAnimator())
            <x-mode-switch :mode="$mode" class="w-full desk:hidden" />
        @endif
    </header>

    @if ($isManaging())
        {{-- Mobile : navigation de gestion en rangée --}}
        <nav class="flex gap-2 overflow-x-auto mx-border-b bg-surface px-4.5 py-2.5 desk:hidden" aria-label="{{ __('components.app_shell.manage_nav') }}">
            @foreach (\App\View\Components\AppShell::MANAGE_SECTIONS as $section_key => $section_route)
                <a href="{{ route($section_route, $club) }}" class="mx-chip flex-none" @if ($section === $section_key) aria-pressed="true" aria-current="page" @endif>
                    {{ __("components.app_shell.sections.{$section_key}") }}
                </a>
            @endforeach
        </nav>
    @endif

    <div class="flex flex-1">
        <aside class="hidden w-60 flex-none flex-col gap-1.5 mx-border-r bg-surface px-3.5 py-5 desk:flex" aria-label="{{ $isManaging() ? __('components.app_shell.manage_nav') : __('components.app_shell.my_clubs') }}">
            @if ($isManaging())
                @foreach (\App\View\Components\AppShell::MANAGE_SECTIONS as $section_key => $section_route)
                    <a href="{{ route($section_route, $club) }}" class="mx-nav-item" @if ($section === $section_key) aria-current="page" @endif>
                        {{ __("components.app_shell.sections.{$section_key}") }}
                    </a>
                @endforeach

                <div class="mt-auto flex flex-col gap-2 mx-border bg-bg p-3.5" x-data="{ copied: false }">
                    <span class="mx-label">{{ __('components.app_shell.invite_code') }}</span>
                    <b class="text-5xl tracking-label">{{ $club->code }}</b>
                    <button
                        type="button"
                        class="mx-btn mx-btn--sm"
                        x-on:click="navigator.clipboard.writeText(@js($club->code)); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <span x-show="! copied">{{ __('components.app_shell.copy_code') }}</span>
                        <span x-show="copied" x-cloak>{{ __('components.app_shell.copied') }}</span>
                    </button>
                </div>
            @else
                <p class="mx-label">{{ __('components.app_shell.my_clubs') }}</p>
                @foreach ($clubs as $member_club)
                    <div class="mx-card flex items-center justify-between gap-1.5 px-2.5 py-2" wire:key="sidebar-club-{{ $member_club->id }}">
                        <b class="truncate">{{ $member_club->name }}</b>
                        @if ($animates($member_club))
                            <span class="mx-badge">{{ __('components.app_shell.animator_badge') }}</span>
                        @endif
                    </div>
                @endforeach
                <a href="{{ route('clubs.create') }}" class="mx-btn mx-btn--sm">{{ __('components.app_shell.create_club') }}</a>
            @endif

            <form method="POST" action="{{ route('logout') }}" @class(['mt-auto' => ! $isManaging()])>
                @csrf
                <button type="submit" class="mx-btn mx-btn--link text-sm">{{ __('components.app_shell.logout') }}</button>
            </form>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <main class="flex-1 px-4.5 py-6 desk:px-8.5 desk:py-7">
                {{ $slot }}
            </main>

            {{-- Message de confirmation après une action (session flash « toast »), masqué après quelques secondes --}}
            @session('toast')
                <div
                    class="pointer-events-none fixed inset-x-0 bottom-5 z-20 flex justify-center px-4.5"
                    x-data="{ shown: true }"
                    x-init="setTimeout(() => shown = false, 3800)"
                    x-show="shown"
                    x-transition.opacity
                >
                    <p class="mx-toast" role="status">{{ $value }}</p>
                </div>
            @endsession

            {{-- Mobile : déconnexion en bas d'écran --}}
            <div class="flex justify-center px-4.5 pb-6 desk:hidden">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mx-btn mx-btn--link text-sm">{{ __('components.app_shell.logout') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
