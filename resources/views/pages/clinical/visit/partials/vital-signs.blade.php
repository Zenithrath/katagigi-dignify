@php
    $vitals = $visit->vitalSign;
    $pregnancyLabels = ['PREGNANT' => 'Hamil', 'NOT_PREGNANT' => 'Tidak Hamil', 'UNSURE' => 'Belum Pasti'];
    $readings = [
        ['Nadi', $vitals?->pulse_bpm !== null ? $vitals->pulse_bpm.' ×/menit' : null],
        ['Suhu', $vitals?->temperature_c !== null ? number_format($vitals->temperature_c, 1, ',', '.').' °C' : null],
        ['Pernapasan', $vitals?->respiratory_rate !== null ? $vitals->respiratory_rate.' ×/menit' : null],
        ['Kehamilan', $vitals?->pregnancy_status ? ($pregnancyLabels[$vitals->pregnancy_status] ?? $vitals->pregnancy_status) : null],
    ];
@endphp

<section class="mt-6 pt-6 border-t border-slate-200">
    <h4 class="text-sm font-bold text-slate-700 mb-1">Tanda Vital</h4>
    <p class="text-xs text-slate-500 mb-3">Nadi, suhu, frekuensi napas, dan status kehamilan. Ikut tersinkron ke SATUSEHAT sebagai Observation (LOINC).</p>

    @if ($vitals)
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            @foreach ($readings as [$label, $value])
                <div class="rounded-xl border border-slate-200 px-4 py-3">
                    <dt class="text-xs text-slate-500">{{ $label }}</dt>
                    <dd class="font-semibold text-slate-900">{{ $value ?? '—' }}</dd>
                </div>
            @endforeach
        </dl>
    @else
        <p class="text-sm text-slate-500 mb-4">Belum dicatat.</p>
    @endif

    @can('write vital sign')
        @unless ($visit->isSigned())
            <form method="post" action="{{ route('visits.vitals.store', $visit->id) }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-1">
                    <div class="input-group">
                        <label for="pulse_bpm">Nadi (×/menit)</label>
                        <input type="number" name="pulse_bpm" id="pulse_bpm" class="custom-input" min="20" max="300"
                            value="{{ old('pulse_bpm', $vitals->pulse_bpm ?? '') }}" />
                        @error('pulse_bpm')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="temperature_c">Suhu (°C)</label>
                        <input type="number" step="0.1" name="temperature_c" id="temperature_c" class="custom-input" min="30" max="45"
                            value="{{ old('temperature_c', $vitals->temperature_c ?? '') }}" />
                        @error('temperature_c')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="respiratory_rate">Pernapasan (×/menit)</label>
                        <input type="number" name="respiratory_rate" id="respiratory_rate" class="custom-input" min="5" max="80"
                            value="{{ old('respiratory_rate', $vitals->respiratory_rate ?? '') }}" />
                        @error('respiratory_rate')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="pregnancy_status">Status kehamilan</label>
                        <select name="pregnancy_status" id="pregnancy_status" class="custom-select">
                            <option value="">—</option>
                            @foreach ($pregnancyLabels as $code => $label)
                                <option value="{{ $code }}" @selected(old('pregnancy_status', $vitals->pregnancy_status ?? '') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('pregnancy_status')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-submit !w-auto !px-8">{{ $vitals ? 'Perbarui' : 'Simpan' }}</button>
                </div>
            </form>
        @endunless
    @endcan
</section>
