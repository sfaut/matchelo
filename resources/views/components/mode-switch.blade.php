@props(['mode' => 'manage'])
{{-- Visible uniquement pour un compte prof animateur --}}
<div {{ $attributes->merge(['class' => 'mx-seg']) }} role="group" aria-label="{{ __('components.mode_switch.label') }}">
    <a href="{{ route('manage.home') }}" class="mx-seg__item" @if($mode === 'manage') aria-current="true" @endif>{{ __('components.mode_switch.manage') }}</a>
    <a href="{{ route('player.home') }}" class="mx-seg__item" @if($mode === 'play') aria-current="true" @endif>{{ __('components.mode_switch.play') }}</a>
</div>
