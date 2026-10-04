@props(['mode' => 'manage'])
{{-- Visible uniquement pour un compte prof animateur --}}
<div {{ $attributes->merge(['class' => 'mx-seg']) }} role="group" aria-label="Mode">
    <a href="{{ route('club.dashboard') }}" class="mx-seg__item" @if($mode === 'manage') aria-current="true" @endif>Gérer le club</a>
    <a href="{{ route('player.home') }}" class="mx-seg__item" @if($mode === 'play') aria-current="true" @endif>Jouer</a>
</div>
