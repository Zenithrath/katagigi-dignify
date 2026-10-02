{{--
    Lengkung odontogram anatomis (SVG) — gigi permanen FDI 11–48.
    Setiap gigi = partial odontogram-tooth (4 zona sisi + tengah + akar).

    Scope Alpine yang dibutuhkan (milik partial tab): `tooth`, `surface`, `tip`, `arch`.
    Variabel lokal: $zoneMap.
--}}
@php
    use App\Helpers\OdontogramChart;

    $arches = [
        'upper' => [
            'label' => 'MAXILLA (RAHANG ATAS)',
            'label_y' => 24,
            'hide_when' => 'lower',
            'teeth' => OdontogramChart::positions('upper'),
        ],
        'lower' => [
            'label' => 'MANDIBLE (RAHANG BAWAH)',
            'label_y' => 724,
            'hide_when' => 'upper',
            'teeth' => OdontogramChart::positions('lower'),
        ],
    ];
@endphp

<svg id="odontogramSvg" class="odontogram-svg" xmlns="http://www.w3.org/2000/svg"
    viewBox="{{ OdontogramChart::VIEW_BOX_BOTH }}" role="group"
    aria-label="Odontogram lengkung gigi permanen"
    x-bind:viewBox="arch === 'upper' ? '{{ OdontogramChart::VIEW_BOX_UPPER }}' : (arch === 'lower' ? '{{ OdontogramChart::VIEW_BOX_LOWER }}' : '{{ OdontogramChart::VIEW_BOX_BOTH }}')">

    @foreach ($arches as $key => $arch)
        <g x-show="arch !== '{{ $arch['hide_when'] }}'">
            <text x="280" y="{{ $arch['label_y'] }}" text-anchor="middle" font-size="12"
                font-weight="700" fill="#64748B" class="odontogram-arch-label">
                {{ $arch['label'] }}
            </text>
            @foreach ($arch['teeth'] as $tooth)
                @include('pages.clinical.visit.partials.odontogram-tooth', $tooth + ['zoneMap' => $zoneMap])
            @endforeach
        </g>
    @endforeach
</svg>
