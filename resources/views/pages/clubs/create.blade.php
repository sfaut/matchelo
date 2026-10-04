<?php

use App\Models\Club;
use App\Models\User\Role;
use App\Support\ClubLogo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Création d'un club : nom, ville, description et logo facultatifs, code d'invitation
 * généré ; le créateur en devient le prof animateur.
 */
new class extends Component
{
    use WithFileUploads;

    /**
     * Nom du club.
     */
    public string $name = '';

    /**
     * Ville du club (facultative).
     */
    public string $city = '';

    /**
     * Jour, heure et lieu des séances (facultatif).
     */
    public string $description = '';

    /**
     * Logo envoyé (facultatif), recadré en carré à l'enregistrement.
     */
    public ?TemporaryUploadedFile $logo = null;

    /**
     * Crée le club et son adhésion d'animateur, enregistre le logo, puis revient à la
     * liste des clubs avec un toast.
     */
    public function create(ClubLogo $club_logo): void
    {
        $this->name = trim($this->name);
        $this->city = trim($this->city);
        $this->description = trim($this->description);

        $this->validate();

        $club = DB::transaction(function () use ($club_logo): Club {
            $user = Auth::user();

            $club = Club::create([
                'name' => $this->name,
                'code' => Club::generateCode($this->name),
                'city' => $this->city === '' ? null : $this->city,
                'description' => $this->description === '' ? null : $this->description,
                'user_id' => $user->id,
            ]);

            $club->members()->attach($user, ['role_id' => Role::idFor(Role::ANIMATOR)]);

            if ($this->logo !== null) {
                $club->update(['logo_path' => $club_logo->store($this->logo, $club)]);
            }

            return $club;
        });

        session()->flash('toast', __('clubs.create.created', ['name' => $club->name]));

        $this->redirectRoute('clubs.index');
    }

    /**
     * Règles de validation du formulaire.
     *
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'city' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }

    /**
     * Libellés des champs dans les messages de validation.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => __('clubs.create.fields.name'),
            'city' => __('clubs.create.fields.city'),
            'description' => __('clubs.create.fields.description'),
            'logo' => __('clubs.create.fields.logo'),
        ];
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
            <a href="{{ route('clubs.index') }}" class="grid size-11 place-items-center text-3xl" aria-label="{{ __('clubs.create.close') }}">✕</a>
            <h1 class="text-2xl font-bold">{{ __('clubs.create.title') }}</h1>
            <span class="size-11" aria-hidden="true"></span>
        </div>

        <div class="flex flex-col items-center gap-1.5">
            <x-image-input id="logo" wire:model="logo" :preview-url="$logo?->isPreviewable() ? $logo->temporaryUrl() : null">
                {{ __('clubs.create.fields.logo') }}
            </x-image-input>
            <p class="text-sm text-muted">{{ __('clubs.create.logo_hint') }}</p>
            @error('logo')
                <p class="mx-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="name" class="mx-label">{{ __('clubs.create.fields.name') }}</label>
            <input id="name" type="text" wire:model="name" maxlength="50" placeholder="{{ __('clubs.create.placeholders.name') }}" class="mx-input" @error('name') aria-invalid="true" @enderror>
            @error('name')
                <p class="mx-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="city" class="mx-label">{{ __('clubs.create.fields.city') }}</label>
            <input id="city" type="text" wire:model="city" maxlength="50" autocomplete="address-level2" placeholder="{{ __('clubs.create.placeholders.city') }}" class="mx-input" @error('city') aria-invalid="true" @enderror>
            @error('city')
                <p class="mx-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="description" class="mx-label">{{ __('clubs.create.fields.description') }}</label>
            <textarea id="description" wire:model="description" rows="3" maxlength="255" placeholder="{{ __('clubs.create.placeholders.description') }}" class="mx-input resize-y" @error('description') aria-invalid="true" @enderror></textarea>
            @error('description')
                <p class="mx-error">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-sm text-muted">{{ __('clubs.create.animator_hint') }}</p>

        <button type="submit" class="mx-btn mx-btn--primary mt-2 w-full p-3.5 text-xl" wire:loading.attr="disabled">
            {{ __('clubs.create.submit') }}
        </button>
    </form>
</x-app-shell>
