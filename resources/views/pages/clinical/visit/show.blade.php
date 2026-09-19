<x-app-layout>
    <x-slot:title>Visit {{ $visit->visit_number }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Visit {{ $visit->visit_number }}</h1>
                <p>{{ $visit->patient->name ?? '-' }} — {{ $visit->doctor->user->name ?? '-' }} — {{ $visit->visit_date?->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge {{ $visit->isSigned() ? 'badge-success' : 'badge-info' }}">{{ $visit->clinical_status }}</span>
                <span class="badge badge-neutral">{{ $visit->billing_status }}</span>
            </div>
        </section>

        <x-flash-alerts />

        <div class="content-card overflow-hidden" x-data="{ tab: 'ringkasan' }">
            <div class="flex gap-2 p-6 pb-0 flex-wrap">
                @foreach (['ringkasan' => 'Ringkasan', 'anamnesis' => 'Anamnesis', 'pemeriksaan' => 'Pemeriksaan', 'odontogram' => 'Odontogram', 'tindakan' => 'Diagnosis & Tindakan', 'resep' => 'Resep', 'rencana' => 'Rencana', 'lampiran' => 'Lampiran'] as $key => $label)
                    <button type="button" x-on:click="tab = '{{ $key }}'"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
                        :class="tab === '{{ $key }}' ? 'bg-emerald-500 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div x-show="tab === 'ringkasan'" class="p-8">
                <dl class="detail-list">
                    <div class="data-container">
                        <dt>Pasien</dt>
                        <dd class="font-semibold">{{ $visit->patient->name ?? '-' }}
                            <span class="text-xs text-slate-400 font-normal ml-1">{{ $visit->patient->code ?? '' }}</span>
                        </dd>
                    </div>
                    <div class="data-container">
                        <dt>Dokter</dt>
                        <dd>{{ $visit->doctor->user->name ?? '-' }}</dd>
                    </div>
                    <div class="data-container">
                        <dt>Cabang</dt>
                        <dd>{{ $visit->branch->name ?? '-' }}</dd>
                    </div>
                    @if ($visit->appointment)
                        <div class="data-container">
                            <dt>Appointment</dt>
                            <dd>
                                <a href="{{ route('appointments.show', $visit->appointment_id) }}" class="text-brand-600 hover:text-brand-700">
                                    {{ $visit->appointment->date }} ({{ $visit->appointment->time_start }}–{{ $visit->appointment->time_end }})
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if ($visit->notes)
                        <div class="data-container">
                            <dt>Catatan</dt>
                            <dd>{{ $visit->notes }}</dd>
                        </div>
                    @endif
                </dl>

                @can('update visit')
                    @if (! $visit->isSigned() && ! empty($transitions[$visit->clinical_status]))
                        <div class="mt-4 pt-4 border-t border-slate-200 flex flex-wrap gap-2">
                            @foreach ($transitions[$visit->clinical_status] as $next)
                                <form action="{{ route('visits.status', $visit->id) }}" method="post">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $next }}" />
                                    <button type="submit" class="clickable-primary px-5 py-2 rounded-xl">→ {{ $next }}</button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                @endcan
            </div>

            <div x-show="tab === 'anamnesis'" class="p-8">
                <h3 class="font-bold text-lg text-slate-900 mb-4">Anamnesis</h3>
                <form method="post" action="{{ $visit->anamnesis ? route('visits.anamnesis.update', $visit->id) : route('visits.anamnesis.store', $visit->id) }}">
                    @csrf
                    @if ($visit->anamnesis)
                        @method('put')
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                        <div class="input-group md:col-span-2">
                            <label for="chief_complaint">Keluhan utama <span class="text-red-500">*</span></label>
                            <textarea name="chief_complaint" id="chief_complaint" rows="3" class="custom-input" required {{ $visit->isSigned() ? 'disabled' : '' }}>{{ old('chief_complaint', $visit->anamnesis->chief_complaint ?? '') }}</textarea>
                            @error('chief_complaint')<small class="danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="input-group md:col-span-2">
                            <label for="present_illness">Riwayat penyakit sekarang</label>
                            <textarea name="present_illness" id="present_illness" rows="3" class="custom-input" {{ $visit->isSigned() ? 'disabled' : '' }}>{{ old('present_illness', $visit->anamnesis->present_illness ?? '') }}</textarea>
                        </div>
                        <div class="input-group">
                            <label for="past_medical_history">Riwayat penyakit dahulu</label>
                            <textarea name="past_medical_history" id="past_medical_history" rows="3" class="custom-input" {{ $visit->isSigned() ? 'disabled' : '' }}>{{ old('past_medical_history', $visit->anamnesis->past_medical_history ?? '') }}</textarea>
                        </div>
                        <div class="input-group">
                            <label for="dental_history">Riwayat gigi</label>
                            <textarea name="dental_history" id="dental_history" rows="3" class="custom-input" {{ $visit->isSigned() ? 'disabled' : '' }}>{{ old('dental_history', $visit->anamnesis->dental_history ?? '') }}</textarea>
                        </div>
                        <div class="input-group">
                            <label for="allergies">Alergi</label>
                            <textarea name="allergies" id="allergies" rows="2" class="custom-input" {{ $visit->isSigned() ? 'disabled' : '' }}>{{ old('allergies', $visit->anamnesis->allergies ?? '') }}</textarea>
                        </div>
                        <div class="input-group">
                            <label for="medications">Obat yang dikonsumsi</label>
                            <textarea name="medications" id="medications" rows="2" class="custom-input" {{ $visit->isSigned() ? 'disabled' : '' }}>{{ old('medications', $visit->anamnesis->medications ?? '') }}</textarea>
                        </div>
                    </div>
                    @can('update visit')
                        @unless ($visit->isSigned())
                            <div class="mt-4 flex justify-end">
                                <button type="submit" class="btn-submit !w-auto !px-8">{{ $visit->anamnesis ? 'Perbarui' : 'Simpan' }}</button>
                            </div>
                        @endunless
                    @endcan
                </form>
            </div>

            <div x-show="tab === 'pemeriksaan'" class="p-8">
                <h3 class="font-bold text-lg text-slate-900 mb-1">Pemeriksaan (SOAP)</h3>
                <p class="text-xs text-slate-500 mb-4">Ditulis dokter. Asisten hanya membaca.</p>
                <form method="post" action="{{ $visit->examination ? route('visits.examination.update', $visit->id) : route('visits.examination.store', $visit->id) }}">
                    @csrf
                    @if ($visit->examination)
                        @method('put')
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                        <div class="input-group">
                            <label for="subjective">Subjective</label>
                            <textarea name="subjective" id="subjective" rows="4" class="custom-input" {{ $visit->isSigned() ? 'disabled' : '' }}>{{ old('subjective', $visit->examination->subjective ?? '') }}</textarea>
                        </div>
                        <div class="input-group">
                            <label for="objective">Objective</label>
                            <textarea name="objective" id="objective" rows="4" class="custom-input" {{ $visit->isSigned() ? 'disabled' : '' }}>{{ old('objective', $visit->examination->objective ?? '') }}</textarea>
                        </div>
                        <div class="input-group">
                            <label for="assessment">Assessment</label>
                            <textarea name="assessment" id="assessment" rows="4" class="custom-input" {{ $visit->isSigned() ? 'disabled' : '' }}>{{ old('assessment', $visit->examination->assessment ?? '') }}</textarea>
                        </div>
                        <div class="input-group">
                            <label for="plan">Plan</label>
                            <textarea name="plan" id="plan" rows="4" class="custom-input" {{ $visit->isSigned() ? 'disabled' : '' }}>{{ old('plan', $visit->examination->plan ?? '') }}</textarea>
                        </div>
                        <div class="input-group">
                            <label for="blood_pressure">Tekanan darah</label>
                            <input type="text" name="blood_pressure" id="blood_pressure" class="custom-input" placeholder="120/80"
                                value="{{ old('blood_pressure', $visit->examination->blood_pressure ?? '') }}" {{ $visit->isSigned() ? 'disabled' : '' }} />
                        </div>
                    </div>
                    @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="btn-submit !w-auto !px-8">{{ $visit->examination ? 'Perbarui' : 'Simpan' }}</button>
                        </div>
                    @endif
                </form>
            </div>

            @foreach (['odontogram' => 'Odontogram', 'tindakan' => 'Diagnosis & Tindakan', 'resep' => 'Resep', 'rencana' => 'Rencana Perawatan', 'lampiran' => 'Lampiran'] as $key => $label)
                <div x-show="tab === '{{ $key }}'" class="p-8">
                    <h3 class="font-bold text-lg text-slate-900 mb-1">{{ $label }}</h3>
                    <p class="text-sm text-slate-500">Menyusul pada task berikutnya.</p>
                </div>
            @endforeach
        </div>
    </main>
</x-app-layout>
