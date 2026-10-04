@props(['present', 'total'])
{{-- Une case par membre : pleine pour un présent, vide sinon (12 par ligne) --}}
<div {{ $attributes->class('grid w-max grid-cols-12 gap-1') }} role="img" aria-label="{{ trans_choice('components.presence_squares.label', $total, ['present' => $present, 'total' => $total]) }}">
    @for ($square = 0; $square < $total; $square++)
        <span @class(['size-3.5 mx-border desk:size-4.5', 'bg-ink' => $square < $present, 'bg-surface' => $square >= $present])></span>
    @endfor
</div>
