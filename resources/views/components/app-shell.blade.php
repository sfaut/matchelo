{{-- Coque de l'application connectée (App\View\Components\AppShell) --}}
<div class="flex min-h-screen flex-col">
    <header class="flex h-16 flex-none items-center justify-between gap-3 mx-border-b bg-surface px-4.5">
        <a href="{{ route('clubs.index') }}" aria-label="{{ __('components.app_shell.home_link') }}">
            <x-logo />
        </a>
        <div class="flex items-center gap-2.5">
            @if ($isAnimator())
                <x-mode-switch :mode="$mode" class="hidden desk:grid" />
            @endif
            <x-scheme-toggle />
            <span class="grid size-8.5 place-items-center mx-border text-xs font-bold" title="{{ __('components.app_shell.account', ['name' => $user->fullName()]) }}">
                {{ $user->initials() }}
            </span>
        </div>
    </header>

    <div class="flex flex-1">
        <aside class="hidden w-55 flex-none flex-col gap-2 mx-border-r px-3 py-4 desk:flex" aria-label="{{ __('components.app_shell.my_clubs') }}">
            <p class="mx-label">{{ __('components.app_shell.my_clubs') }}</p>
            @foreach ($clubs as $club)
                <div class="mx-card flex items-center justify-between gap-1.5 px-2.5 py-2" wire:key="sidebar-club-{{ $club->id }}">
                    <b class="truncate">{{ $club->name }}</b>
                    @if ($animates($club))
                        <span class="mx-badge">{{ __('components.app_shell.animator_badge') }}</span>
                    @endif
                </div>
            @endforeach
            <a href="{{ route('clubs.create') }}" class="mx-btn mx-btn--sm">{{ __('components.app_shell.create_club') }}</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                @csrf
                <button type="submit" class="mx-btn mx-btn--link text-sm">{{ __('components.app_shell.logout') }}</button>
            </form>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <main class="flex-1 px-4.5 py-6 desk:px-6.5">
                {{ $slot }}
            </main>

            {{-- Mobile : switch de mode et déconnexion en bas d'écran --}}
            <div class="flex flex-col items-center gap-3 px-4.5 pb-6 desk:hidden">
                @if ($isAnimator())
                    <x-mode-switch :mode="$mode" class="w-full" />
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mx-btn mx-btn--link text-sm">{{ __('components.app_shell.logout') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
