@props(['id'])
{{-- Champ mot de passe avec bouton Afficher / Masquer (Alpine) ; les attributs (wire:model, autocomplete…) vont au champ --}}
<div x-data="{ visible: false }" class="flex mx-border rounded-theme bg-surface focus-within:shadow-solid">
    <input
        id="{{ $id }}"
        type="password"
        x-bind:type="visible ? 'text' : 'password'"
        {{ $attributes->class('min-w-0 flex-1 bg-transparent px-3.5 py-3 text-lg leading-snug text-ink outline-none placeholder:text-muted') }}
    >
    <button
        type="button"
        class="px-3.5 text-md font-bold underline"
        aria-controls="{{ $id }}"
        aria-pressed="false"
        x-bind:aria-pressed="visible"
        x-on:click="visible = ! visible"
    >
        <span x-show="! visible">{{ __('components.password_input.show') }}</span>
        <span x-show="visible" x-cloak>{{ __('components.password_input.hide') }}</span>
    </button>
</div>
