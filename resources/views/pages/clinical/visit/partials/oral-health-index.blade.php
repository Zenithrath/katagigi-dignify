@php
    $ohi = $visit->oralHealthIndex;
    $interpretation = $ohi?->interpretation();
    // Warna badge diturunkan dari kode interpretasi, bukan ambang terpisah.
    $badgeClass = match ($interpretation[0] ?? null) {
        'OI000029' => 'badge-success',
        'OI000030' => 'badge-warning',
        default => 'badge-danger',
    };
@endphp

<section class="mt-6 pt-6 border-t border-slate-200">
    <h4 class="text-sm font-bold text-slate-700 mb-1">OHI-S &amp; DMF-T</h4>
    <p class="text-xs text-slate-500 mb-3">Indeks kebersihan mulut dan pengalaman karies. Ambang interpretasi mengikuti kategori Kemenkes (OI000029–OI000031).</p>

    @if ($ohi)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="rounded-xl border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-slate-700">OHI-S</span>
                    @if ($interpretation)
                        <span class="badge {{ $badgeClass }}">
                            {{ $interpretation[1] }}
                        </span>
                    @endif
                </div>
                <dl class="text-sm text-slate-600 space-y-1">
                    <div class="flex justify-between"><dt>Debris</dt><dd class="font-semibold text-slate-900">{{ $ohi->ohis_debris ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt>Kalkulus</dt><dd class="font-semibold text-slate-900">{{ $ohi->ohis_calculus ?? '—' }}</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-1"><dt>Skor total</dt><dd class="font-semibold text-slate-900">{{ $ohi->ohis_total ?? '—' }}</dd></div>
                </dl>
            </div>
            <div class="rounded-xl border border-slate-200 p-4">
                <span class="text-sm font-semibold text-slate-700">DMF-T</span>
                <dl class="text-sm text-slate-600 space-y-1 mt-2">
                    <div class="flex justify-between"><dt>Decayed (D)</dt><dd class="font-semibold text-slate-900">{{ $ohi->d_count ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt>Missing (M)</dt><dd class="font-semibold text-slate-900">{{ $ohi->m_count ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt>Filled (F)</dt><dd class="font-semibold text-slate-900">{{ $ohi->f_count ?? '—' }}</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-1"><dt>Indeks DMF-T</dt><dd class="font-semibold text-slate-900">{{ $ohi->dmt_index ?? '—' }}</dd></div>
                </dl>
            </div>
        </div>
        @if ($ohi->notes)
            <p class="text-sm text-slate-600 mb-4">{{ $ohi->notes }}</p>
        @endif
    @else
        <p class="text-sm text-slate-500 mb-4">Belum diukur.</p>
    @endif

    @can('write oral health index')
        @unless ($visit->isSigned())
            <form method="post" action="{{ route('visits.oral-health.store', $visit->id) }}">
                @csrf
                <div class="grid grid-cols-2 md:grid-cols-5 gap-x-6 gap-y-1">
                    <div class="input-group">
                        <label for="ohis_debris">Debris (0–5)</label>
                        <input type="number" step="0.1" name="ohis_debris" id="ohis_debris" class="custom-input" min="0" max="5"
                            value="{{ old('ohis_debris', $ohi->ohis_debris ?? '') }}" />
                        @error('ohis_debris')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="ohis_calculus">Kalkulus (0–5)</label>
                        <input type="number" step="0.1" name="ohis_calculus" id="ohis_calculus" class="custom-input" min="0" max="5"
                            value="{{ old('ohis_calculus', $ohi->ohis_calculus ?? '') }}" />
                        @error('ohis_calculus')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="d_count">Gigi D</label>
                        <input type="number" name="d_count" id="d_count" class="custom-input" min="0" max="32"
                            value="{{ old('d_count', $ohi->d_count ?? '') }}" />
                        @error('d_count')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="m_count">Gigi M</label>
                        <input type="number" name="m_count" id="m_count" class="custom-input" min="0" max="32"
                            value="{{ old('m_count', $ohi->m_count ?? '') }}" />
                        @error('m_count')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="f_count">Gigi F</label>
                        <input type="number" name="f_count" id="f_count" class="custom-input" min="0" max="32"
                            value="{{ old('f_count', $ohi->f_count ?? '') }}" />
                        @error('f_count')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="input-group mt-2">
                    <label for="ohi_notes">Catatan</label>
                    <input type="text" name="notes" id="ohi_notes" class="custom-input" value="{{ old('notes', $ohi->notes ?? '') }}" />
                    @error('notes')<small class="danger">{{ $message }}</small>@enderror
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-submit !w-auto !px-8">{{ $ohi ? 'Perbarui' : 'Simpan' }}</button>
                </div>
            </form>
        @endunless
    @endcan
</section>
