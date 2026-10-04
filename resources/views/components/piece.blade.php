@props(['type' => 'king', 'side' => 'white'])
{{-- Pièce validée (cartouche 1a) · viewBox 24 · contour 1,6 couleur opposée · paint-order stroke
     side="current" : pièce pleine en currentColor, sans contour (icônes, pastilles) --}}
@php
$paths = [
    'king' => '<polygon points="11,1 13,1 13,3 15.5,3 15.5,5 13,5 13,7.5 11,7.5 11,5 8.5,5 8.5,3 11,3"/><polygon points="5.5,8.5 18.5,8.5 16.5,15 7.5,15"/><polygon points="7.5,15.5 16.5,15.5 16,18.5 8,18.5"/><rect x="5" y="19" width="14" height="3"/>',
    'queen' => '<polygon points="7,15.5 17,15.5 16.5,18.5 7.5,18.5"/><rect x="5" y="19" width="14" height="3"/><polygon points="4.5,7 7,11.5 9.5,5.5 12,10.5 14.5,5.5 17,11.5 19.5,7 17.5,15 6.5,15"/><circle cx="4.5" cy="5" r="1.6"/><circle cx="9.5" cy="3.5" r="1.6"/><circle cx="14.5" cy="3.5" r="1.6"/><circle cx="19.5" cy="5" r="1.6"/>',
    'rook' => '<polygon points="7.5,8.5 16.5,8.5 17,18.5 7,18.5"/><rect x="5" y="19" width="14" height="3"/><polygon points="6,2.5 9,2.5 9,4.5 10.5,4.5 10.5,2.5 13.5,2.5 13.5,4.5 15,4.5 15,2.5 18,2.5 18,8 6,8"/>',
    'bishop' => '<polygon points="9.5,14 14.5,14 16,18.5 8,18.5"/><rect x="5" y="19" width="14" height="3"/><path d="M12 4.2 L13.6 6.26 L11 9.6 L11.9 10.3 L14.25 7.1 L16.5 10 L15.5 13.5 L8.5 13.5 L7.5 10 Z"/><circle cx="12" cy="3.4" r="2"/>',
    'knight' => '<path fill-rule="evenodd" d="M7.5 18.5 L8.5 13 L5.5 11.5 L5.5 8.5 L10 4.5 L10.5 1.5 L13 3.5 C16.5 4 18.5 7 18.5 11 L18 18.5 Z M11.5 7 L13.5 7 L13.5 9 L11.5 9 Z"/><rect x="5" y="19" width="14" height="3"/>',
    'pawn' => '<polygon points="9.5,13 14.5,13 16,18.5 8,18.5"/><rect x="5" y="19" width="14" height="3"/><circle cx="12" cy="6.5" r="3.5"/><rect x="8" y="10.5" width="8" height="2"/>',
];
$is_white = $side === 'white';
$is_current = $side === 'current';
// Taille par défaut size-8, sauf si l'appelant fournit sa propre classe size-*
$classes = ['block', 'size-8' => ! preg_match('/(^|\s|:)size-/', $attributes->get('class', ''))];
@endphp
@if ($is_current)
<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" {{ $attributes->class($classes) }}>{!! $paths[$type] !!}</svg>
@else
<svg viewBox="0 0 24 24" fill="{{ $is_white ? '#ffffff' : '#0c0c0c' }}" stroke="{{ $is_white ? '#0c0c0c' : '#ffffff' }}" stroke-width="1.6" stroke-linejoin="miter" paint-order="stroke" aria-hidden="true" {{ $attributes->class($classes) }}>{!! $paths[$type] !!}</svg>
@endif
