@props(['bars'])
{{-- Histogramme d'assiduité : une barre par séance, hauteur = taux de présence ; la séance du jour
     est surlignée. bars : list<array{label: string, rate: int, is_today: bool}> --}}
<div {{ $attributes->class('flex flex-col gap-1.5') }}>
    <div class="grid h-37.5 items-end gap-3.5" style="grid-template-columns: repeat({{ max(1, count($bars)) }}, minmax(0, 1fr))">
        @foreach ($bars as $bar)
            <div class="flex h-full flex-col justify-end gap-1.5">
                <b class="text-center text-sm">{{ Number::percentage($bar['rate']) }}</b>
                <div @class(['mx-border', 'bg-highlight' => $bar['is_today'], 'bg-ink' => ! $bar['is_today']]) style="height: {{ max(2, $bar['rate']) }}%"></div>
            </div>
        @endforeach
    </div>
    <div class="grid gap-3.5 text-center text-sm text-muted" style="grid-template-columns: repeat({{ max(1, count($bars)) }}, minmax(0, 1fr))">
        @foreach ($bars as $bar)
            <span>{{ $bar['label'] }}</span>
        @endforeach
    </div>
</div>
