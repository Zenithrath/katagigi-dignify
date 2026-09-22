<x-app-layout>
    <x-slot:title>Visit {{ $visit->visit_number }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <x-back-button href="{{ route('visits.index') }}" />
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
                @can('create transaction')
                    @if ($visit->appointment_id && $visit->billing_status === 'UNBILLED')
                        @php $openInvoice = $visit->invoices->firstWhere('status', 'DRAFT') ?? $visit->invoices->firstWhere('status', 'ISSUED') ?? $visit->invoices->firstWhere('status', 'PARTIALLY_PAID'); @endphp
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if ($openInvoice)
                                <a href="{{ route('invoices.show', $openInvoice->id) }}" class="clickable-primary px-5 py-2 rounded-xl">Lanjut tagihan {{ $openInvoice->number }}</a>
                            @else
                                <form action="{{ route('visits.invoice.store', $visit->id) }}" method="post">
                                    @csrf
                                    <button type="submit" class="clickable-primary px-5 py-2 rounded-xl">Buat tagihan dari visit</button>
                                </form>
                            @endif
                            <a href="{{ route('transactions.create', ['visit' => $visit->id]) }}" class="clickable-ghost px-5 py-2 rounded-xl">Nota lama (prefill)</a>
                        </div>
                    @endif
                @endcan
                @if ($visit->isSigned())
                    <p class="mt-3 text-xs text-slate-500">Ditandatangani oleh {{ $visit->signer->name ?? '-' }} pada {{ $visit->signed_at?->format('d M Y H:i') }}. Visit terkunci.</p>
                    @can('manage satusehat')
                        <form action="{{ route('visits.satusehat.sync', $visit->id) }}" method="post" class="mt-2">
                            @csrf
                            <button type="submit" class="clickable-ghost px-5 py-2 rounded-xl text-sm">Sinkron SATUSEHAT</button>
                        </form>
                    @endcan
                @elseif ($visit->clinical_status === 'DONE')
                    @can('sign visit')
                        <form action="{{ route('visits.sign', $visit->id) }}" method="post" class="mt-3">
                            @csrf
                            <button type="submit" class="clickable-primary px-5 py-2.5 rounded-xl"
                                @click.prevent="if(confirm('Tandatangani dan kunci visit ini?')) $el.closest('form').submit()">
                                Tandatangani visit
                            </button>
                        </form>
                    @endcan
                @endif
                @if ($visit->satusehatLogs->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($visit->satusehatLogs as $log)
                            <span class="badge {{ $log->status === 'SUCCESS' ? 'badge-success' : ($log->status === 'FAILED' ? 'badge-danger' : 'badge-neutral') }}">{{ $log->resource_type }}: {{ $log->status }}</span>
                        @endforeach
                    </div>
                @endif
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

            <div x-show="tab === 'odontogram'" class="p-8" x-data="{ tooth: '{{ request('tooth', '') }}', surface: 'whole' }">
                <h3 class="font-bold text-lg text-slate-900 mb-1">Odontogram (FDI)</h3>
                <p class="text-xs text-slate-500 mb-4">Klik zona gigi untuk memilih permukaan, atau klik nomor gigi untuk seluruh gigi. Warna mengikuti kondisi terakhir.</p>

                @include('pages.clinical.visit.partials.odontogram-chart')

                @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                    <form method="post" action="{{ route('visits.odontogram.store', $visit->id) }}" class="mt-6 pt-6 border-t border-slate-200">
                        @csrf
                        <h4 class="text-sm font-bold text-slate-700 mb-3">Catat temuan <span x-text="tooth ? 'gigi ' + tooth + ' (' + surface + ')' : '(pilih gigi di chart)'" class="text-emerald-600"></span></h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1">
                            <input type="hidden" name="fdi" :value="tooth" />
                            <div class="input-group">
                                <label for="condition">Kondisi <span class="text-red-500">*</span></label>
                                <select name="condition" id="condition" class="custom-select" required>
                                    @foreach (\App\Models\OdontogramFinding::CONDITIONS as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('condition')<small class="danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="input-group">
                                <label for="surface">Permukaan</label>
                                <select name="surface" id="surface" class="custom-select" x-model="surface">
                                    @foreach (\App\Models\OdontogramFinding::SURFACES as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-group">
                                <label for="material">Material</label>
                                <input type="text" name="material" id="material" class="custom-input" placeholder="mis. komposit, GIC" />
                            </div>
                            <div class="input-group md:col-span-3">
                                <label for="notes">Catatan</label>
                                <input type="text" name="notes" id="notes" class="custom-input" />
                            </div>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="submit" class="btn-submit !w-auto !px-8">Simpan temuan</button>
                        </div>
                        @error('fdi')<small class="danger">{{ $message }}</small>@enderror
                        @error('notes')<small class="danger">{{ $message }}</small>@enderror
                    </form>
                @endif

                @if ($visit->odontogramFindings->isNotEmpty())
                    <div class="mt-6">
                        <h4 class="text-sm font-bold text-slate-700 mb-2">Temuan tercatat ({{ $visit->odontogramFindings->count() }})</h4>
                        <ul class="divide-y divide-slate-100">
                            @foreach ($visit->odontogramFindings->sortBy([['fdi', 'asc'], ['surface', 'asc']]) as $finding)
                                <li class="py-2 flex items-center justify-between gap-3 text-sm">
                                    <span>
                                        <span class="font-bold">{{ $finding->fdi }}</span>
                                        <span class="text-slate-500">{{ \App\Models\OdontogramFinding::SURFACES[$finding->surface] ?? $finding->surface }} —</span>
                                        <span class="font-medium">{{ \App\Models\OdontogramFinding::CONDITIONS[$finding->condition] ?? $finding->condition }}</span>
                                        @if ($finding->material)<span class="text-slate-500">({{ $finding->material }})</span>@endif
                                        @if ($finding->notes)<span class="text-slate-400">· {{ $finding->notes }}</span>@endif
                                    </span>
                                    @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                                        <form action="{{ route('visits.odontogram.destroy', [$visit->id, $finding->id]) }}" method="post"
                                            @submit.prevent="if(confirm('Hapus temuan ini?')) $el.submit()">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div x-show="tab === 'tindakan'" class="p-8 space-y-8">
                <section>
                    <h3 class="font-bold text-lg text-slate-900 mb-1">Diagnosis Penyakit (ICD-10)</h3>
                    <p class="text-xs text-slate-500 mb-3">Minimal 1 per visit — syarat SATUSEHAT. Tandai primer/sekunder.</p>
                    @if ($visit->diagnoses->isNotEmpty())
                        <ul class="divide-y divide-slate-100 mb-4">
                            @foreach ($visit->diagnoses->sortBy([['is_primary', 'desc'], ['code', 'asc']]) as $dx)
                                <li class="py-2 flex items-center justify-between gap-3 text-sm">
                                    <span>
                                        @if ($dx->tooth_fdi)<span class="font-bold">{{ $dx->tooth_fdi }}</span>@endif
                                        <span class="font-semibold">[{{ $dx->code }}]</span> {{ $dx->display }}
                                        <span class="ml-1 text-xs px-2 py-0.5 rounded-md {{ $dx->is_primary ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $dx->is_primary ? 'Primer' : 'Sekunder' }}</span>
                                    </span>
                                    @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                                        <form action="{{ route('visits.diagnoses.destroy', [$visit->id, $dx->id]) }}" method="post"
                                            @submit.prevent="if(confirm('Hapus diagnosis ini?')) $el.submit()">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                        <form method="post" action="{{ route('visits.diagnoses.store', $visit->id) }}" class="rounded-xl border border-slate-200 p-4">
                            @csrf
                            <livewire:diagnosis-search system="ICD10" fieldName="diagnosis_code_ids" :selected="[]" />
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1 mt-3">
                                <div class="input-group">
                                    <label>Gigi (opsional)</label>
                                    <select name="tooth_fdi" class="custom-select">
                                        <option value="">— Tanpa gigi spesifik —</option>
                                        @foreach (\App\Models\OdontogramFinding::allTeeth() as $fdi)
                                            <option value="{{ $fdi }}">{{ $fdi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label class="flex items-center gap-2 cursor-pointer select-none mt-7">
                                        <input type="checkbox" name="is_primary" value="1" checked class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                                        <span class="text-sm font-medium text-slate-700">Diagnosis primer</span>
                                    </label>
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <button type="submit" class="btn-submit !w-auto !px-8">Tambah diagnosis</button>
                            </div>
                            @error('diagnosis_code_ids')<small class="danger">{{ $message }}</small>@enderror
                        </form>
                    @endif
                </section>

                <section class="pt-6 border-t border-slate-200">
                    <h3 class="font-bold text-lg text-slate-900 mb-1">Tindakan / Prosedur (ICD-9-CM)</h3>
                    <p class="text-xs text-slate-500 mb-3">Ditagih pada nota (prefill). Harga satuan dalam rupiah.</p>
                    @if ($visit->treatments->isNotEmpty())
                        <ul class="divide-y divide-slate-100 mb-4">
                            @foreach ($visit->treatments->sortBy('code') as $tx)
                                <li class="py-2 flex items-center justify-between gap-3 text-sm">
                                    <span>
                                        @if ($tx->tooth_fdi)<span class="font-bold">{{ $tx->tooth_fdi }}</span>@endif
                                        <span class="font-semibold">[{{ $tx->code }}]</span> {{ $tx->procedure }}
                                        <span class="text-slate-500">{{ $tx->quantity }} × Rp{{ number_format($tx->unit_price, 0, ',', '.') }}</span>
                                    </span>
                                    @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                                        <form action="{{ route('visits.treatments.destroy', [$visit->id, $tx->id]) }}" method="post"
                                            @submit.prevent="if(confirm('Hapus tindakan ini?')) $el.submit()">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <p class="text-sm font-bold text-slate-900 mb-4">Estimasi: Rp{{ number_format($visit->treatments->sum(fn ($t) => $t->quantity * $t->unit_price), 0, ',', '.') }}</p>
                    @endif
                    @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                        <form method="post" action="{{ route('visits.treatments.store', $visit->id) }}" class="rounded-xl border border-slate-200 p-4">
                            @csrf
                            <livewire:diagnosis-search system="ICD9" fieldName="procedure_code_ids" :selected="[]" />
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-1 mt-3">
                                <div class="input-group">
                                    <label>Gigi (opsional)</label>
                                    <select name="tooth_fdi" class="custom-select">
                                        <option value="">— Tanpa gigi spesifik —</option>
                                        @foreach (\App\Models\OdontogramFinding::allTeeth() as $fdi)
                                            <option value="{{ $fdi }}">{{ $fdi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label for="quantity">Jumlah</label>
                                    <input type="number" name="quantity" id="quantity" class="custom-input" value="1" min="1" />
                                </div>
                                <div class="input-group md:col-span-2">
                                    <label for="unit_price">Harga satuan (Rp)</label>
                                    <input type="number" name="unit_price" id="unit_price" class="custom-input" value="0" min="0" />
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <button type="submit" class="btn-submit !w-auto !px-8">Tambah tindakan</button>
                            </div>
                            @error('procedure_code_ids')<small class="danger">{{ $message }}</small>@enderror
                        </form>
                    @endif
                </section>
            </div>

            <div x-show="tab === 'rencana'" class="p-8 space-y-6">
                <h3 class="font-bold text-lg text-slate-900 mb-1">Rencana Perawatan</h3>
                @forelse ($visit->treatmentPlans as $plan)
                    <section class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <div>
                                <p class="font-bold text-slate-900">{{ $plan->title }}</p>
                                <p class="text-xs text-slate-500">Estimasi total: Rp{{ number_format($plan->estimatedTotal(), 0, ',', '.') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-info">{{ $plan->status }}</span>
                                @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                                    <form action="{{ route('visits.plans.status', [$visit->id, $plan->id]) }}" method="post" class="flex items-center gap-1">
                                        @csrf
                                        <select name="status" class="custom-select !py-1 !px-2 !text-xs" onchange="this.form.submit()">
                                            @foreach (\App\Models\TreatmentPlan::STATUSES as $s)
                                                <option value="{{ $s }}" @selected($plan->status === $s)>{{ $s }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                    <form action="{{ route('visits.plans.destroy', [$visit->id, $plan->id]) }}" method="post"
                                        @submit.prevent="if(confirm('Hapus rencana ini?')) $el.submit()">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @if ($plan->notes)<p class="text-sm text-slate-600 mt-1">{{ $plan->notes }}</p>@endif
                        <ul class="divide-y divide-slate-100 mt-2">
                            @foreach ($plan->items as $item)
                                <li class="py-2 flex items-center justify-between gap-3 text-sm">
                                    <span>
                                        <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 mr-1">P{{ $item->priority }}</span>
                                        @if ($item->tooth_fdi)<span class="font-bold">{{ $item->tooth_fdi }}</span>@endif
                                        {{ $item->description }}
                                        <span class="text-slate-500">· Rp{{ number_format($item->estimated_price, 0, ',', '.') }}</span>
                                    </span>
                                    @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                                        <form action="{{ route('visits.plans.items.destroy', [$visit->id, $plan->id, $item->id]) }}" method="post"
                                            @submit.prevent="if(confirm('Hapus item ini?')) $el.submit()">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                            <form method="post" action="{{ route('visits.plans.items.store', [$visit->id, $plan->id]) }}" class="mt-3 grid grid-cols-1 md:grid-cols-5 gap-2">
                                @csrf
                                <select name="tooth_fdi" class="custom-select">
                                    <option value="">Gigi —</option>
                                    @foreach (\App\Models\OdontogramFinding::allTeeth() as $fdi)
                                        <option value="{{ $fdi }}">{{ $fdi }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="description" class="custom-input md:col-span-2" placeholder="Tindakan direncanakan *" required />
                                <input type="number" name="estimated_price" class="custom-input" placeholder="Estimasi Rp" min="0" value="0" />
                                <button type="submit" class="clickable-primary px-4 py-2 rounded-xl text-sm">+ Item</button>
                            </form>
                        @endif
                    </section>
                @empty
                    <p class="text-sm text-slate-500">Belum ada rencana perawatan.</p>
                @endforelse
                @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                    <form method="post" action="{{ route('visits.plans.store', $visit->id) }}" class="rounded-xl border border-slate-200 p-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                            <div class="input-group">
                                <label for="plan_title">Judul rencana <span class="text-red-500">*</span></label>
                                <input type="text" name="title" id="plan_title" class="custom-input" placeholder="mis. Rehabilitasi rahang atas tahap 1" required />
                            </div>
                            <div class="input-group">
                                <label for="plan_notes">Catatan</label>
                                <input type="text" name="notes" id="plan_notes" class="custom-input" />
                            </div>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="submit" class="btn-submit !w-auto !px-8">Buat rencana</button>
                        </div>
                    </form>
                @endif
            </div>

            <div x-show="tab === 'resep'" class="p-8 space-y-6">
                <h3 class="font-bold text-lg text-slate-900 mb-1">Resep</h3>
                @forelse ($visit->prescriptions as $rx)
                    <section class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-bold text-slate-900">Resep — {{ $rx->prescribed_at?->format('d M Y') }}</p>
                            @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                                <form action="{{ route('visits.prescriptions.destroy', [$visit->id, $rx->id]) }}" method="post"
                                    @submit.prevent="if(confirm('Hapus resep ini?')) $el.submit()">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                </form>
                            @endif
                        </div>
                        @if ($rx->notes)<p class="text-sm text-slate-600 mt-1">{{ $rx->notes }}</p>@endif
                        <ul class="divide-y divide-slate-100 mt-2">
                            @foreach ($rx->items as $item)
                                <li class="py-2 flex items-center justify-between gap-3 text-sm">
                                    <span>
                                        <span class="font-semibold">{{ $item->medicine_name }}</span>
                                        @if ($item->kfa_code)<span class="text-xs text-slate-400 ml-1">[{{ $item->kfa_code }}]</span>@endif
                                        <span class="text-slate-500 block text-xs mt-0.5">
                                            {{ collect([$item->dosage, $item->frequency, $item->duration, $item->quantity . ' pcs', $item->route])->filter()->join(' · ') }}
                                            @if ($item->instruction)— {{ $item->instruction }}@endif
                                        </span>
                                    </span>
                                    @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                                        <form action="{{ route('visits.prescriptions.items.destroy', [$visit->id, $rx->id, $item->id]) }}" method="post"
                                            @submit.prevent="if(confirm('Hapus obat ini?')) $el.submit()">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                            <form method="post" action="{{ route('visits.prescriptions.items.store', [$visit->id, $rx->id]) }}" class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-2">
                                @csrf
                                <input type="text" name="medicine_name" class="custom-input md:col-span-2" placeholder="Nama obat *" required />
                                <input type="text" name="kfa_code" class="custom-input" placeholder="Kode KFA" />
                                <input type="text" name="dosage" class="custom-input" placeholder="Dosis (500 mg)" />
                                <input type="text" name="frequency" class="custom-input" placeholder="Frekuensi (3× sehari)" />
                                <input type="text" name="duration" class="custom-input" placeholder="Durasi (5 hari)" />
                                <input type="number" name="quantity" class="custom-input" placeholder="Jumlah" min="1" value="1" />
                                <input type="text" name="instruction" class="custom-input md:col-span-3" placeholder="Aturan pakai (sesudah makan)" />
                                <button type="submit" class="clickable-primary px-4 py-2 rounded-xl text-sm">+ Obat</button>
                            </form>
                        @endif
                    </section>
                @empty
                    <p class="text-sm text-slate-500">Belum ada resep.</p>
                @endforelse
                @if (auth()->user()->hasRole(['doctor', 'manajemen']) && ! $visit->isSigned())
                    <form method="post" action="{{ route('visits.prescriptions.store', $visit->id) }}" class="flex flex-wrap items-end gap-2">
                        @csrf
                        <div class="input-group !mb-0">
                            <label for="prescribed_at">Tanggal</label>
                            <input type="date" name="prescribed_at" id="prescribed_at" class="custom-input" value="{{ date('Y-m-d') }}" />
                        </div>
                        <button type="submit" class="btn-submit !w-auto !px-8">Buat resep</button>
                    </form>
                @endif
            </div>

            <div x-show="tab === 'lampiran'" class="p-8 space-y-6">
                <h3 class="font-bold text-lg text-slate-900 mb-1">Lampiran</h3>
                <p class="text-xs text-slate-500">Disimpan privat — hanya bisa dibuka lewat tautan bertanda tangan.</p>
                @forelse ($visit->attachments as $file)
                    <section class="rounded-xl border border-slate-200 p-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900 text-sm">
                                <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 mr-1">{{ \App\Models\VisitAttachment::TYPES[$file->type] ?? $file->type }}</span>
                                {{ $file->description ?? basename($file->path) }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">oleh {{ $file->uploader->name ?? '-' }} · {{ $file->created_at?->format('d M Y H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ URL::temporarySignedRoute('attachments.file', now()->addMinutes(30), ['attachment' => $file->id]) }}"
                                target="_blank" class="text-sm text-brand-600 hover:text-brand-700">Buka</a>
                            @can('update visit')
                                @unless ($visit->isSigned())
                                    <form action="{{ route('visits.attachments.destroy', [$visit->id, $file->id]) }}" method="post"
                                        @submit.prevent="if(confirm('Hapus lampiran ini?')) $el.submit()">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                    </form>
                                @endunless
                            @endcan
                        </div>
                    </section>
                @empty
                    <p class="text-sm text-slate-500">Belum ada lampiran.</p>
                @endforelse
                @can('update visit')
                    @unless ($visit->isSigned())
                        <form method="post" action="{{ route('visits.attachments.store', $visit->id) }}" enctype="multipart/form-data" class="rounded-xl border border-slate-200 p-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1">
                                <div class="input-group">
                                    <label for="file_type">Jenis <span class="text-red-500">*</span></label>
                                    <select name="type" id="file_type" class="custom-select">
                                        @foreach (\App\Models\VisitAttachment::TYPES as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')<small class="danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="input-group">
                                    <label for="file">Berkas (jpg/png/webp/pdf, maks 10MB) <span class="text-red-500">*</span></label>
                                    <input type="file" name="file" id="file" class="custom-input" required />
                                    @error('file')<small class="danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="input-group">
                                    <label for="file_description">Keterangan</label>
                                    <input type="text" name="description" id="file_description" class="custom-input" />
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <button type="submit" class="btn-submit !w-auto !px-8">Unggah</button>
                            </div>
                        </form>
                    @endunless
                @endcan
            </div>
        </div>
    </main>
</x-app-layout>
