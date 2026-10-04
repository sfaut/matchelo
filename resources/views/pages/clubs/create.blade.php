<?php

use App\Livewire\Forms\ClubForm;
use App\Support\ClubLogo;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Création d'un club : nom, ville, description et logo facultatifs, code d'invitation
 * généré ; le créateur en devient le prof animateur.
 */
new class extends Component
{
    use WithFileUploads;

    /**
     * Formulaire du club.
     */
    public ClubForm $form;

    /**
     * Crée le club, puis revient à la liste des clubs avec un toast.
     */
    public function create(ClubLogo $club_logo): void
    {
        $club = $this->form->create(Auth::user(), $club_logo);

        session()->flash('toast', __('clubs.create.created', ['name' => $club->name]));

        $this->redirectRoute('clubs.index');
    }

    /**
     * Rend la page, avec son titre.
     */
    public function render(): View
    {
        return $this->view()->title(__('clubs.create.title'));
    }
};
?>

<x-app-shell mode="play">
    <form wire:submit="create" class="mx-auto flex max-w-115 flex-col gap-4" novalidate>
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('clubs.index') }}" class="grid size-11 place-items-center text-3xl" aria-label="{{ __('clubs.form.close') }}">✕</a>
            <h1 class="text-2xl font-bold">{{ __('clubs.create.title') }}</h1>
            <span class="size-11" aria-hidden="true"></span>
        </div>

        <x-club-fields :form="$form" />

        <p class="text-sm text-muted">{{ __('clubs.create.animator_hint') }}</p>

        <button type="submit" class="mx-btn mx-btn--primary mt-2 w-full p-3.5 text-xl" wire:loading.attr="disabled">
            {{ __('clubs.create.submit') }}
        </button>
    </form>
</x-app-shell>
