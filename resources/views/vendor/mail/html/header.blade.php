@props(['url'])
{{-- En-tête des e-mails : logo PNG (les messageries bloquent souvent le SVG) et mot « matchelo » --}}
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('images/mail/logo.png') }}" class="logo" width="38" height="38" alt="">
<span class="wordmark">matchelo</span>
</a>
</td>
</tr>
