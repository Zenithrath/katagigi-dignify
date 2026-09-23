{{--
    Panel odontogram lengkap: filter lengkung, chart anatomis + tooltip,
    gigi sulung, legenda warna, dan ekspor (JSON/SVG/PNG).

    Wajib di-include DI DALAM elemen ber-`x-data` yang menyediakan:
      tooth, surface, arch, tip { show, fdi, type, universal, palmer, left, top }.
    Lihat halaman visit (tab Odontogram).

    Variabel lokal: $visit (dengan relasi odontogramFindings).
--}}
@php
    use App\Helpers\OdontogramChart;
    use App\Models\OdontogramFinding;

    $zoneMap = OdontogramChart::zoneMap($visit->odontogramFindings);
    $deciduous = [
        'upper' => OdontogramChart::positions('upper', true),
        'lower' => OdontogramChart::positions('lower', true),
    ];
    $findingsExport = $visit->odontogramFindings
        ->map(fn ($f) => [
            'fdi' => $f->fdi,
            'surface' => $f->surface,
            'condition' => $f->condition,
            'material' => $f->material,
            'notes' => $f->notes,
        ])
        ->values()
        ->all();
@endphp

<div class="odontogram-panel">
    <style>
        .odontogram-panel .odontogram-svg { width: 100%; height: auto; user-select: none; display: block; }
        .odontogram-panel .odontogram-stage {
            background: #06070a; border: 1px solid #222736; border-radius: 14px;
            padding: 12px; overflow-x: auto;
        }
        .odontogram-panel .odontogram-tooth { cursor: pointer; }
        .odontogram-panel .odontogram-tooth:focus { outline: none; }
        .odontogram-panel .odontogram-tooth:hover .odontogram-surface { filter: brightness(1.18); }
        .odontogram-panel .odontogram-tooth:focus-visible .odontogram-surface { filter: brightness(1.18); }
        .odontogram-panel .odontogram-label { pointer-events: none; }
        .odontogram-tooltip {
            position: fixed; z-index: 70; pointer-events: none; display: block;
            background: #f8fafc; color: #0f172a; border: 1px solid #cbd5e1;
            border-radius: 10px; padding: 8px 12px; font-size: 12px; line-height: 1.5;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .3); white-space: nowrap;
        }
        .odontogram-panel .odontogram-btn {
            border: 1px solid #cbd5e1; background: #fff; color: #475569;
            border-radius: 10px; padding: 6px 12px; font-size: 12px; font-weight: 600;
            cursor: pointer;
        }
        .odontogram-panel .odontogram-btn:hover { color: #0f172a; border-color: #94a3b8; }
        .odontogram-panel details > summary { list-style: none; cursor: pointer; }
        .odontogram-panel details > summary::-webkit-details-marker { display: none; }
    </style>

    {{-- Toolbar: filter lengkung + ekspor --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="inline-flex gap-1 rounded-xl bg-slate-100 p-1">
            @foreach (['both' => 'Atas & Bawah', 'upper' => 'Rahang Atas', 'lower' => 'Rahang Bawah'] as $key => $label)
                <button type="button" x-on:click="arch = '{{ $key }}'"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                    x-bind:class="arch === '{{ $key }}' ? 'bg-emerald-500 text-white shadow' : 'text-slate-600 hover:bg-slate-200'">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="odontogram-btn" x-on:click="window.odontogramExport && window.odontogramExport('json')">Ekspor JSON</button>
            <button type="button" class="odontogram-btn" x-on:click="window.odontogramExport && window.odontogramExport('svg')">SVG</button>
            <button type="button" class="odontogram-btn" x-on:click="window.odontogramExport && window.odontogramExport('png')">PNG</button>
        </div>
    </div>

    <div class="odontogram-stage">
        @include('pages.clinical.visit.partials.odontogram-chart', ['zoneMap' => $zoneMap])
    </div>

    {{-- Gigi sulung: lengkung terpisah agar tidak tumpang tindih --}}
    <details class="mt-4 rounded-xl border border-slate-200">
        <summary class="px-4 py-2 text-sm font-semibold text-slate-600">Gigi sulung (FDI 51–85)</summary>
        <div class="odontogram-stage mt-2" style="border-radius: 0 0 14px 14px;">
            <svg id="odontogramSvgDeciduous" class="odontogram-svg" xmlns="http://www.w3.org/2000/svg"
                viewBox="{{ OdontogramChart::VIEW_BOX_BOTH }}" role="group"
                aria-label="Odontogram lengkung gigi sulung">
                @foreach (['upper' => 'MAXILLA (RAHANG ATAS)', 'lower' => 'MANDIBLE (RAHANG BAWAH)'] as $arch => $label)
                    <text x="280" y="{{ $arch === 'upper' ? 24 : 724 }}" text-anchor="middle" font-size="12"
                        font-weight="700" fill="#64748B">{{ $label }}</text>
                    @foreach ($deciduous[$arch] as $tooth)
                        @include('pages.clinical.visit.partials.odontogram-tooth', $tooth + ['zoneMap' => $zoneMap])
                    @endforeach
                @endforeach
            </svg>
        </div>
    </details>

    {{-- Legenda warna kondisi (selaras dengan OdontogramFinding::CONDITIONS) --}}
    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-slate-600">
        @foreach (OdontogramFinding::CONDITIONS as $code => $label)
            <span class="inline-flex items-center gap-1.5">
                <span class="h-3 w-3 rounded-sm border border-slate-300"
                    style="background: {{ OdontogramChart::FILLS[$code] ?? '#fff' }}"></span>{{ $label }}
            </span>
        @endforeach
    </div>
</div>

{{-- Tooltip melayang: menampilkan notasi FDI/Universal/Palmer --}}
<div x-show="tip.show" x-cloak class="odontogram-tooltip"
    x-bind:style="`left: ${tip.left}px; top: ${tip.top}px`">
    <div><strong>Tooth:</strong> <span x-text="tip.fdi"></span></div>
    <div><strong>Type:</strong> <span x-text="tip.type"></span></div>
    <div><strong>Universal:</strong> <span x-text="tip.universal"></span>, <strong>Palmer:</strong> <span x-text="tip.palmer"></span></div>
</div>

<script type="application/json" id="odontogramFindingsData">@json($findingsExport)</script>

@once
    <script>
        window.odontogramExport = function (kind) {
            const node = document.getElementById('odontogramSvg');
            if (!node) return;

            const stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '');
            const save = function (blob, ext) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'odontogram-' + stamp + '.' + ext;
                a.click();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
            };

            if (kind === 'json') {
                const el = document.getElementById('odontogramFindingsData');
                const findings = el ? JSON.parse(el.textContent || '[]') : [];
                save(new Blob([JSON.stringify({
                    layout: 'arch_anatomical_fdi',
                    generatedAt: new Date().toISOString(),
                    findings: findings,
                }, null, 2)], { type: 'application/json' }), 'json');
                return;
            }

            const source = new XMLSerializer().serializeToString(node);
            if (kind === 'svg') {
                save(new Blob([source], { type: 'image/svg+xml' }), 'svg');
                return;
            }

            const img = new Image();
            img.onload = function () {
                const canvas = document.createElement('canvas');
                canvas.width = 1252;
                canvas.height = 1480;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                canvas.toBlob((blob) => blob && save(blob, 'png'), 'image/png');
            };
            img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(source);
        };
    </script>
@endonce
