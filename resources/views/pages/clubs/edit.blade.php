<?php

use App\Livewire\Forms\ClubForm;
use App\Models\Club;
use App\Support\ClubLogo;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Modification d'un club par l'un de ses animateurs ou un administrateur (ClubPolicy::update) :
 * nom, ville, description et logo ; le code d'invitation est affiché, pas modifiable.
 */
new class extends Component
{
    use WithFileUploads;

    /**
     * Club modifié.
     */
    #[Locked]
    public Club $club;

    /**
     * Formulaire du club.
     */
    public ClubForm $form;

    /**
     * Vérifie le droit de modifier le club, puis remplit le formulaire.
     *
     * @throws AuthorizationException si l'utilisateur n'anime pas le club et n'est pas administrateur
     */
    public function mount(Club $club): void
    {
        $this->authorize('update', $club);

        $this->club = $club;
        $this->form->fillFrom($club);
    }

    /**
     * Enregistre les modifications, puis revient à la liste des clubs avec un toast.
     *
     * @throws AuthorizationException si le droit a été retiré entre-temps
     */
    public function save(ClubLogo $club_logo): void
    {
        $this->authorize('update', $this->club);

        $this->form->update($this->club, $club_logo);

        session()->flash('toast', __('clubs.edit.updated', ['name' => $this->club->name]));

        $this->redirectRoute('clubs.index');
    }

    /**
     * Rend la page, avec son titre.
     */
    public function render(): View
    {
        return $this->view()->title(__('clubs.edit.title'));
    }
};
?>

<x-app-shell mode="play">
    <form wire:submit="save" class="mx-auto flex max-w-115 flex-col gap-4" novalidate>
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('clubs.index') }}" class="grid size-11 place-items-center text-3xl" aria-label="{{ __('clubs.form.close') }}">✕</a>
            <h1 class="text-2xl font-bold">{{ __('clubs.edit.title') }}</h1>
            <span class="size-11" aria-hidden="true"></span>
        </div>

        <x-club-fields :form="$form" :current-logo-url="$club->logoUrl()" />

        <div class="mx-card mx-card--highlight flex flex-col gap-1">
            <span class="mx-label">{{ __('clubs.edit.code') }}</span>
            <span class="text-figure-sm tracking-heading">{{ $club->code }}</span>
            <span class="text-sm">{{ __('clubs.edit.code_hint') }}</span>
        </div>

        <button type="submit" class="mx-btn mx-btn--primary mt-2 w-full p-3.5 text-xl" wire:loading.attr="disabled">
            {{ __('clubs.edit.submit') }}
        </button>
    </form>
</x-app-shell>
