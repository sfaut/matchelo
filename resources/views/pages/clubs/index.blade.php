<?php

use App\Models\Club;
use App\Models\User\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Accueil de l'utilisateur connecté : la liste de ses clubs.
 */
new class extends Component
{
    /**
     * Clubs de l'utilisateur, par nom, avec le nombre de membres.
     *
     * @return Collection<int, Club>
     */
    #[Computed]
    public function clubs(): Collection
    {
        return Auth::user()->clubs()->withCount('members')->orderBy('name')->get();
    }

    /**
     * Clubs que l'utilisateur anime, pour le badge « prof ».
     *
     * @return Collection<int, Club>
     */
    #[Computed]
    public function animatedClubs(): Collection
    {
        return Auth::user()->clubs()
            ->wherePivot('role_id', Role::idFor(Role::ANIMATOR))
            ->get();
    }

    /**
     * Rend la page, avec son titre.
     */
    public function render(): View
    {
        return $this->view()->title(__('clubs.index.title'));
    }
};
?>

<x-app-shell mode="play">
    <div class="flex max-w-180 flex-col gap-3">
        <h1 class="text-title">{{ __('clubs.index.title') }}</h1>

        @forelse ($this->clubs as $club)
            <article class="mx-card flex flex-col gap-1" wire:key="club-{{ $club->id }}">
                <div class="flex items-center gap-3">
                    @if ($club->logoUrl())
                        <img src="{{ $club->logoUrl() }}" alt="" class="size-10 flex-none mx-border object-cover">
                    @endif
                    <h2 class="min-w-0 flex-1 text-2xl font-bold">{{ $club->name }}</h2>
                    @if ($this->animatedClubs->contains($club))
                        <span class="mx-badge">{{ __('clubs.index.animator_badge') }}</span>
                    @endif
                </div>
                <p class="text-sm text-muted">{{ trans_choice('clubs.index.members', $club->members_count) }}</p>
            </article>
        @empty
            <p class="text-md text-muted">{{ __('clubs.index.empty') }}</p>
        @endforelse

        <a href="{{ route('clubs.create') }}" class="mx-btn">{{ __('clubs.index.create') }}</a>
    </div>
</x-app-shell>
