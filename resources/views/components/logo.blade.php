@props(['inverse' => false])
<span {{ $attributes->merge(['class' => 'mx-logo'.($inverse ? ' mx-logo--inverse' : '')]) }}>
    <span class="mx-logo__mark"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="11,1 13,1 13,3 15.5,3 15.5,5 13,5 13,7.5 11,7.5 11,5 8.5,5 8.5,3 11,3"/><polygon points="5.5,8.5 18.5,8.5 16.5,15 7.5,15"/><polygon points="7.5,15.5 16.5,15.5 16,18.5 8,18.5"/><rect x="5" y="19" width="14" height="3"/></svg></span>
    <span class="mx-logo__word">matchelo</span>
</span>
