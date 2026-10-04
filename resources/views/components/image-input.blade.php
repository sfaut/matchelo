@props(['id', 'previewUrl' => null])
{{-- Choix d'une image carrée (logo) : aperçu, sinon case vide portant le libellé du slot ;
     les attributs (wire:model…) vont au champ fichier, masqué mais accessible au clavier --}}
<label
    for="{{ $id }}"
    class="relative grid size-19 cursor-pointer place-items-center overflow-hidden mx-border bg-surface-2 text-xs font-bold text-muted focus-within:shadow-solid"
>
    @if ($previewUrl)
        <img src="{{ $previewUrl }}" alt="" class="size-full object-cover">
    @else
        <span>{{ $slot }}</span>
    @endif
    <input id="{{ $id }}" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" {{ $attributes }}>
</label>
