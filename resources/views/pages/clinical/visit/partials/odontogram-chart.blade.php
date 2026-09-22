{{--
  Chart odontogram SVG interaktif (gaya coolhead/react-odontogram).
  - Tiap gigi = 5 zona klik: 4 sisi + tengah (oklusal/insisal).
  - Klik zona mengisi state Alpine `tooth` + `surface` milik form di bawah.
  - Klik nomor FDI = pilih seluruh gigi (surface 'whole').
  - Warna zona mengikuti temuan terakhir; gigi hilang ditandai silang (X).
  - Memakai scope Alpine parent (wajib di-include di dalam elemen x-data tooth/surface).

  Variabel yang dibutuhkan: $visit (dengan relasi odontogramFindings).
--}}
@php
    use App\Models\OdontogramFinding;

    // Warna fill SVG per kondisi (diselaraskan dengan CHART_COLORS).
    $fills = [
        'sound' => '#A7F3D0', 'caries' => '#FCA5A5', 'filled' => '#93C5FD',
        'crown' => '#FCD34D', 'missing' => '#E2E8F0', 'implant' => '#C4B5FD',
        'denture' => '#5EEAD4', 'root' => '#FDBA74', 'mobile' => '#FDE047',
        'fracture' => '#FDA4AF',
    ];
    $defaultFill = '#FFFFFF';

    // Peta temuan per gigi per permukaan (terakhir menang).
    $zoneMap = [];
    foreach ($visit->odontogramFindings->sortBy('created_at') as $f) {
        $zoneMap[$f->fdi][$f->surface ?? 'whole'] = $f->condition;
    }
    $zoneCondition = fn ($fdi, $surface) => $zoneMap[$fdi][$surface] ?? $zoneMap[$fdi]['whole'] ?? null;
    $zoneFill = fn ($fdi, $surface) => $fills[$zoneCondition($fdi, $surface) ?? ''] ?? $defaultFill;

    // Gigi geraham/premolar pakai oklusal, depan pakai insisal.
    $isBackTooth = fn ($fdi) => (bool) preg_match('/^(1[4-8]|2[4-8]|3[4-8]|4[4-8]|5[45]|6[45]|7[45]|8[45])$/', $fdi);

    // Sisi mesial menghadap garis tengah. Kembalikan [kiri, kanan, atas, bawah].
    $sidesFor = function ($quadrant) {
        $mesialRight = in_array($quadrant, ['upper_right', 'lower_right'], true);
        $left = $mesialRight ? 'distal' : 'mesial';
        $right = $mesialRight ? 'mesial' : 'distal';
        $isUpper = str_starts_with($quadrant, 'upper');
        $top = $isUpper ? 'buccal' : 'lingual';
        $bottom = $isUpper ? 'palatal' : 'buccal';
        return [$left, $right, $top, $bottom];
    };
@endphp

<div class="space-y-5">
    @foreach (['upper' => 'Rahang atas', 'lower' => 'Rahang bawah'] as $arch => $archLabel)
        <div>
            <p class="text-xs font-semibold text-slate-500 mb-2">{{ $archLabel }}</p>
            <div class="flex items-stretch gap-1 overflow-x-auto pb-1">
                @foreach (['right', 'left'] as $side)
                    @php
                        $quadrant = $arch . '_' . $side;
                        $teeth = OdontogramFinding::PERMANENT[$quadrant];
                        [$zLeft, $zRight, $zTop, $zBottom] = $sidesFor($quadrant);
                    @endphp
                    <div class="flex gap-1">
                        @foreach ($teeth as $fdi)
                            @php
                                $center = $isBackTooth($fdi) ? 'occlusal' : 'incisal';
                                $isMissing = ($zoneCondition($fdi, 'whole') === 'missing');
                            @endphp
                            <div class="flex flex-col items-center">
                                <svg viewBox="0 0 48 64" class="w-11 h-14 cursor-pointer rounded"
                                    :class="(tooth === '{{ $fdi }}' && surface === 'whole') ? 'ring-2 ring-emerald-500' : ''"
                                    role="button" tabindex="0" aria-label="Gigi {{ $fdi }}"
                                    @click="tooth = '{{ $fdi }}'; surface = 'whole'">
                                    @foreach (['top' => $zTop, 'bottom' => $zBottom, 'left' => $zLeft, 'right' => $zRight] as $pos => $surface)
                                        @php
                                            $points = match ($pos) {
                                                'top' => '8,5 40,5 32,17 16,17',
                                                'bottom' => '8,47 40,47 32,35 16,35',
                                                'left' => '5,9 17,17 17,35 5,43',
                                                'right' => '43,9 31,17 31,35 43,43',
                                            };
                                        @endphp
                                        <polygon points="{{ $points }}" fill="{{ $zoneFill($fdi, $surface) }}"
                                            @click.stop="tooth = '{{ $fdi }}'; surface = '{{ $surface }}'"
                                            :stroke="(tooth === '{{ $fdi }}' && surface === '{{ $surface }}') ? '#059669' : '#94a3b8'"
                                            stroke-width="1.5" class="hover:opacity-80">
                                            <title>{{ $fdi }} — {{ OdontogramFinding::SURFACES[$surface] }}</title>
                                        </polygon>
                                    @endforeach
                                    <rect x="16" y="17" width="16" height="18" rx="2" fill="{{ $zoneFill($fdi, $center) }}"
                                        @click.stop="tooth = '{{ $fdi }}'; surface = '{{ $center }}'"
                                        :stroke="(tooth === '{{ $fdi }}' && surface === '{{ $center }}') ? '#059669' : '#94a3b8'"
                                        stroke-width="1.5" class="hover:opacity-80">
                                        <title>{{ $fdi }} — {{ OdontogramFinding::SURFACES[$center] }}</title>
                                    </rect>
                                    @if ($isMissing)
                                        <line x1="8" y1="8" x2="40" y2="44" stroke="#64748b" stroke-width="2.5" />
                                        <line x1="40" y1="8" x2="8" y2="44" stroke="#64748b" stroke-width="2.5" />
                                    @endif
                                    <text x="24" y="60" text-anchor="middle" font-size="9" font-weight="700" fill="#334155">{{ $fdi }}</text>
                                </svg>
                            </div>
                        @endforeach
                    </div>
                    @if ($side === 'right')
                        <div class="w-px self-stretch bg-slate-300 mx-1" title="Garis tengah"></div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach

    <details class="rounded-xl border border-slate-200">
        <summary class="cursor-pointer px-4 py-2 text-sm font-semibold text-slate-600">Gigi sulung (51–85)</summary>
        <div class="p-4 pt-2 space-y-4">
            @foreach (['upper' => 'Atas', 'lower' => 'Bawah'] as $arch => $archLabel)
                <div class="flex items-stretch gap-1 overflow-x-auto pb-1">
                    @foreach (['right', 'left'] as $side)
                        @php
                            $quadrant = $arch . '_' . $side;
                            $teeth = OdontogramFinding::DECIDUOUS[$quadrant];
                            [$zLeft, $zRight, $zTop, $zBottom] = $sidesFor($quadrant);
                        @endphp
                        <div class="flex gap-1">
                            @foreach ($teeth as $fdi)
                                @php
                                    $center = $isBackTooth($fdi) ? 'occlusal' : 'incisal';
                                    $isMissing = ($zoneCondition($fdi, 'whole') === 'missing');
                                @endphp
                                <div class="flex flex-col items-center">
                                    <svg viewBox="0 0 48 64" class="w-10 h-12 cursor-pointer rounded"
                                        :class="(tooth === '{{ $fdi }}' && surface === 'whole') ? 'ring-2 ring-emerald-500' : ''"
                                        role="button" tabindex="0" aria-label="Gigi {{ $fdi }}"
                                        @click="tooth = '{{ $fdi }}'; surface = 'whole'">
                                        @foreach (['top' => $zTop, 'bottom' => $zBottom, 'left' => $zLeft, 'right' => $zRight] as $pos => $surface)
                                            @php
                                                $points = match ($pos) {
                                                    'top' => '8,5 40,5 32,17 16,17',
                                                    'bottom' => '8,47 40,47 32,35 16,35',
                                                    'left' => '5,9 17,17 17,35 5,43',
                                                    'right' => '43,9 31,17 31,35 43,43',
                                                };
                                            @endphp
                                            <polygon points="{{ $points }}" fill="{{ $zoneFill($fdi, $surface) }}"
                                                @click.stop="tooth = '{{ $fdi }}'; surface = '{{ $surface }}'"
                                                :stroke="(tooth === '{{ $fdi }}' && surface === '{{ $surface }}') ? '#059669' : '#94a3b8'"
                                                stroke-width="1.5" class="hover:opacity-80">
                                                <title>{{ $fdi }} — {{ OdontogramFinding::SURFACES[$surface] }}</title>
                                            </polygon>
                                        @endforeach
                                        <rect x="16" y="17" width="16" height="18" rx="2" fill="{{ $zoneFill($fdi, $center) }}"
                                            @click.stop="tooth = '{{ $fdi }}'; surface = '{{ $center }}'"
                                            :stroke="(tooth === '{{ $fdi }}' && surface === '{{ $center }}') ? '#059669' : '#94a3b8'"
                                            stroke-width="1.5" class="hover:opacity-80">
                                            <title>{{ $fdi }} — {{ OdontogramFinding::SURFACES[$center] }}</title>
                                        </rect>
                                        @if ($isMissing)
                                            <line x1="8" y1="8" x2="40" y2="44" stroke="#64748b" stroke-width="2.5" />
                                            <line x1="40" y1="8" x2="8" y2="44" stroke="#64748b" stroke-width="2.5" />
                                        @endif
                                        <text x="24" y="60" text-anchor="middle" font-size="9" font-weight="700" fill="#334155">{{ $fdi }}</text>
                                    </svg>
                                </div>
                            @endforeach
                        </div>
                        @if ($side === 'right')
                            <div class="w-px self-stretch bg-slate-300 mx-1" title="Garis tengah"></div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    </details>

    {{-- Legenda kondisi --}}
    <div class="flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-slate-600">
        @foreach (OdontogramFinding::CONDITIONS as $code => $label)
            @php $dot = explode(' ', OdontogramFinding::CHART_COLORS[$code])[0]; @endphp
            <span class="inline-flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm border border-slate-300 {{ $dot }}"></span>{{ $label }}
            </span>
        @endforeach
    </div>
</div>
