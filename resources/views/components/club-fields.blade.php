@props(['form', 'currentLogoUrl' => null])
{{-- Champs d'un club (App\Livewire\Forms\ClubForm, propriété « form » du composant Livewire) :
     logo, nom, ville, description. currentLogoUrl : logo enregistré, en modification. --}}
@php
    $preview_url = match (true) {
        $form->logo?->isPreviewable() ?? false => $form->logo->temporaryUrl(),
        $form->removeLogo => null,
        default => $currentLogoUrl,
    };
@endphp
<div class="flex flex-col items-center gap-1.5">
    <x-image-input id="logo" wire:model="form.logo" :preview-url="$preview_url">
        {{ __('clubs.form.fields.logo') }}
    </x-image-input>
    <p class="text-sm text-muted">{{ __('clubs.form.logo_hint') }}</p>
    @if ($currentLogoUrl && ! $form->removeLogo && ! $form->logo)
        <button type="button" class="mx-btn mx-btn--link text-sm" wire:click="$set('form.removeLogo', true)">
            {{ __('clubs.form.remove_logo') }}
        </button>
    @endif
    @error('form.logo')
        <p class="mx-error">{{ $message }}</p>
    @enderror
</div>

<div class="flex flex-col gap-1.5">
    <label for="name" class="mx-label">{{ __('clubs.form.fields.name') }}</label>
    <input id="name" type="text" wire:model="form.name" maxlength="50" placeholder="{{ __('clubs.form.placeholders.name') }}" class="mx-input" @error('form.name') aria-invalid="true" @enderror>
    @error('form.name')
        <p class="mx-error">{{ $message }}</p>
    @enderror
</div>

<div class="flex flex-col gap-1.5">
    <label for="city" class="mx-label">{{ __('clubs.form.fields.city') }}</label>
    <input id="city" type="text" wire:model="form.city" maxlength="50" autocomplete="address-level2" placeholder="{{ __('clubs.form.placeholders.city') }}" class="mx-input" @error('form.city') aria-invalid="true" @enderror>
    @error('form.city')
        <p class="mx-error">{{ $message }}</p>
    @enderror
</div>

<div class="flex flex-col gap-1.5">
    <label for="description" class="mx-label">{{ __('clubs.form.fields.description') }}</label>
    <textarea id="description" wire:model="form.description" rows="3" maxlength="300" placeholder="{{ __('clubs.form.placeholders.description') }}" class="mx-input resize-y" @error('form.description') aria-invalid="true" @enderror></textarea>
    @error('form.description')
        <p class="mx-error">{{ $message }}</p>
    @enderror
</div>
