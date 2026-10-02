{{--
    Satu gigi anatomis pada lengkung odontogram.
    Zona klik: 4 sisi + tengah (oklusal/insisal); klik gigi = surface 'whole'.

    Scope Alpine yang dibutuhkan (milik partial tab): `tooth`, `surface`, `tip`.
    Variabel lokal: $fdi, $x, $y, $scale (opsional), $zoneMap.
--}}
@php
    use App\Helpers\OdontogramChart;
    use App\Models\OdontogramFinding;

    $meta = OdontogramChart::meta((string) $fdi);
    $shape = OdontogramChart::shape($meta['type']);
    $angle = OdontogramChart::angle((float) $x, (float) $y);
    $scale = (float) ($scale ?? 1);
    $surfaces = $meta['surfaces'];
    $missing = ($zoneMap[$fdi]['whole'] ?? null) === 'missing';
    $sides = [
        'top' => $shape['top'],
        'bottom' => $shape['bottom'],
        'left' => $shape['left'],
        'right' => $shape['right'],
    ];
    $center = $shape['center'];
    $surfaceLabel = fn (string $surface) => OdontogramFinding::SURFACES[$surface] ?? $surface;
@endphp

<g class="odontogram-tooth"
   transform="translate({{ $x }} {{ $y }}) rotate({{ $angle }})"
   data-fdi="{{ $fdi }}"
   data-type="{{ $meta['type_label'] }}"
   data-universal="{{ $meta['universal'] }}"
   data-palmer="{{ $meta['palmer'] }}"
   role="button" tabindex="0"
   aria-label="Gigi {{ $fdi }} — {{ $meta['type_label'] }}"
   x-on:mouseenter="tip = { show: true, fdi: $el.dataset.fdi, type: $el.dataset.type, universal: $el.dataset.universal, palmer: $el.dataset.palmer, left: $event.clientX + 14, top: $event.clientY + 14 }"
   x-on:mousemove="tip.left = $event.clientX + 14; tip.top = $event.clientY + 14"
   x-on:mouseleave="tip.show = false"
   x-on:click="tooth = '{{ $fdi }}'; surface = 'whole'"
   x-on:keydown.enter.prevent="tooth = '{{ $fdi }}'; surface = 'whole'">

    <g transform="scale({{ $scale }})">
        <path d="{{ $shape['root'] }}" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="1.2"
            stroke-dasharray="2 2" />

        @foreach ($sides as $zone => $d)
            <path d="{{ $d }}" class="odontogram-surface" stroke="#94A3B8" stroke-width="1"
                fill="{{ OdontogramChart::fillFor($zoneMap, $fdi, $surfaces[$zone]) }}"
                x-bind:stroke="tooth === '{{ $fdi }}' && surface === '{{ $surfaces[$zone] }}' ? '#059669' : null"
                x-bind:stroke-width="tooth === '{{ $fdi }}' && surface === '{{ $surfaces[$zone] }}' ? 2.5 : null"
                x-on:click.stop="tooth = '{{ $fdi }}'; surface = '{{ $surfaces[$zone] }}'">
                <title>{{ $fdi }} — {{ $surfaceLabel($surfaces[$zone]) }}</title>
            </path>
        @endforeach

        <rect x="{{ $center['x'] }}" y="{{ $center['y'] }}" width="{{ $center['w'] }}"
            height="{{ $center['h'] }}" rx="{{ $center['rx'] }}" class="odontogram-surface"
            stroke="#94A3B8" stroke-width="1"
            fill="{{ OdontogramChart::fillFor($zoneMap, $fdi, $surfaces['center']) }}"
            x-bind:stroke="tooth === '{{ $fdi }}' && surface === '{{ $surfaces['center'] }}' ? '#059669' : null"
            x-bind:stroke-width="tooth === '{{ $fdi }}' && surface === '{{ $surfaces['center'] }}' ? 2.5 : null"
            x-on:click.stop="tooth = '{{ $fdi }}'; surface = '{{ $surfaces['center'] }}'">
            <title>{{ $fdi }} — {{ $surfaceLabel($surfaces['center']) }}</title>
        </rect>

        @if ($missing)
            <line x1="-15" y1="-15" x2="15" y2="15" stroke="#ef4444" stroke-width="3" />
            <line x1="15" y1="-15" x2="-15" y2="15" stroke="#ef4444" stroke-width="3" />
        @endif
    </g>

    <text x="0" y="26" transform="rotate({{ -$angle }})" text-anchor="middle"
        dominant-baseline="middle" font-size="11" font-weight="700" fill="#334155"
        class="odontogram-label"
        x-bind:fill="tooth === '{{ $fdi }}' ? '#059669' : null">{{ $fdi }}</text>
</g>
