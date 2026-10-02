{{-- Drawer triase: panel geser kanan di workspace, tanpa pindah halaman.
     Form vital + keluhan submit ke route existing (back() → tetap di sini). --}}
@canany(['write vital sign', 'write anamnesis'])
<div x-show="drawer === '{{ $visit->id }}'" class="fixed inset-0 z-40" style="display: none;">
    <div class="absolute inset-0 bg-slate-900/50" x-on:click="drawer = null"></div>
    <aside class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-xl overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
            <div>
                <p class="text-xs font-semibold text-slate-500">TRIASE {{ $visit->queueLabel() }}</p>
                <p class="text-base font-bold text-slate-900">{{ $visit->patient->name ?? '-' }}</p>
                <p class="text-xs text-slate-400">No. RM {{ $visit->patient->code ?? '-' }}</p>
            </div>
            <button type="button" x-on:click="drawer = null" class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100" aria-label="Tutup">
                <span class="text-lg leading-none">×</span>
            </button>
        </div>

        <div class="p-5 space-y-6">
            {{-- Data lengkap pasien saat check-in (read-only). --}}
            <section>
                <h4 class="text-sm font-bold text-slate-700 mb-2">Data Pasien</h4>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <div><dt class="text-xs text-slate-500">NIK</dt><dd class="font-semibold text-slate-900">{{ $visit->patient->nik ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">No. HP</dt><dd class="font-semibold text-slate-900">{{ $visit->patient->phone ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Tanggal lahir</dt><dd class="font-semibold text-slate-900">{{ $visit->patient->birthdate ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Jenis kelamin</dt><dd class="font-semibold text-slate-900">{{ $visit->patient->gender ?? '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-xs text-slate-500">Alamat</dt><dd class="font-semibold text-slate-900">{{ collect([$visit->patient->address->street ?? null, $visit->patient->address->village ?? null, $visit->patient->address->district ?? null])->filter()->join(', ') ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Dokter</dt><dd class="font-semibold text-slate-900">{{ $visit->doctor->user->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Jadwal</dt><dd class="font-semibold text-slate-900">@if ($visit->appointment){{ $visit->appointment->date }} ({{ substr($visit->appointment->time_start ?? '', 0, 5) }}–{{ substr($visit->appointment->time_end ?? '', 0, 5) }})@else—@endif</dd></div>
                </dl>
            </section>

            @can('write vital sign')
                <form method="post" action="{{ route('visits.vitals.store', $visit->id) }}">
                    @csrf
                    {{-- Triase dari workspace: simpan → otomatis masuk antrean dokter. --}}
                    <input type="hidden" name="advance_to_called" value="1" />
                    <h4 class="text-sm font-bold text-slate-700 mb-2">Tanda Vital</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="input-group !mb-0">
                            <label for="pulse_{{ $visit->id }}">Nadi (×/mnt)</label>
                            <input type="number" name="pulse_bpm" id="pulse_{{ $visit->id }}" class="custom-input" min="20" max="300"
                                value="{{ $visit->vitalSign->pulse_bpm ?? '' }}" />
                        </div>
                        <div class="input-group !mb-0">
                            <label for="temp_{{ $visit->id }}">Suhu (°C)</label>
                            <input type="number" step="0.1" name="temperature_c" id="temp_{{ $visit->id }}" class="custom-input" min="30" max="45"
                                value="{{ $visit->vitalSign->temperature_c ?? '' }}" />
                        </div>
                        <div class="input-group !mb-0">
                            <label for="resp_{{ $visit->id }}">Napas (×/mnt)</label>
                            <input type="number" name="respiratory_rate" id="resp_{{ $visit->id }}" class="custom-input" min="5" max="80"
                                value="{{ $visit->vitalSign->respiratory_rate ?? '' }}" />
                        </div>
                        <div class="input-group !mb-0">
                            <label for="preg_{{ $visit->id }}">Kehamilan</label>
                            <select name="pregnancy_status" id="preg_{{ $visit->id }}" class="custom-select">
                                <option value="">—</option>
                                @foreach (['PREGNANT' => 'Hamil', 'NOT_PREGNANT' => 'Tidak Hamil', 'UNSURE' => 'Belum Pasti'] as $code => $label)
                                    <option value="{{ $code }}" @selected(($visit->vitalSign->pregnancy_status ?? '') === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="submit" class="btn-submit !w-auto !px-6">Simpan &amp; ke Dokter</button>
                    </div>
                </form>
            @endcan

            @can('write anamnesis')
                <form method="post" action="{{ $visit->anamnesis ? route('visits.anamnesis.update', $visit->id) : route('visits.anamnesis.store', $visit->id) }}">
                    @csrf
                    <input type="hidden" name="advance_to_called" value="1" />
                    @if ($visit->anamnesis)
                        @method('put')
                    @endif
                    <h4 class="text-sm font-bold text-slate-700 mb-2">Keluhan Utama</h4>
                    <div class="input-group !mb-0">
                        <textarea name="chief_complaint" rows="3" class="custom-input" required>{{ $visit->anamnesis->chief_complaint ?? '' }}</textarea>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="submit" class="btn-submit !w-auto !px-6">{{ $visit->anamnesis ? 'Perbarui' : 'Simpan' }} &amp; ke Dokter</button>
                    </div>
                </form>
            @endcan

            <a href="{{ route('visits.show', $visit->id) }}" wire:navigate class="block text-center text-sm text-brand-600 hover:text-brand-700">Buka halaman visit lengkap →</a>
        </div>
    </aside>
</div>
@endcanany
