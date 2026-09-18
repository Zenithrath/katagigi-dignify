<x-app-layout>
    <x-slot:title>{{ __('patient.record.detail.index._title') }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('Medical Record') }} #{{ strtoupper(substr($data->id, 0, 7)) }}</h1>
                <p>{{ \Carbon\Carbon::parse($data->created_at)->locale('id')->setTimezone('Asia/Jakarta')->isoFormat('dddd, DD MMMM YYYY HH:mm ZZ') }}</p>
            </div>
        </section>

        <section class="content-card">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1">
                <dl class="detail-list">
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('patient.record.detail.data.patient.title') }}</dt>
                        <dd class="flex flex-col gap-0">
                            <span class="font-semibold text-slate-900">{{ $data->patient_name }}</span>
                            <span class="text-sm text-slate-500">{{ __('patient.record.detail.data.patient.id') }}: {{ $data->patient_code }}</span>
                        </dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('patient.record.detail.data.doctor.title') }}</dt>
                        <dd class="flex flex-col gap-0">
                            <span class="font-semibold text-slate-900">{{ $data->doctor_name }}</span>
                            <span class="text-sm text-slate-500">{{ __('patient.record.detail.data.doctor.nipp') }}: {{ $data->doctor_nipp }}</span>
                        </dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('patient.record.detail.data.recomendation') }}</dt>
                        <dd class="font-medium text-slate-900">
                            {{ \Carbon\Carbon::parse($data->next_schedule)->locale('id')->setTimezone('Asia/Jakarta')->isoFormat('DD MMMM YYYY') }}
                        </dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('patient.record.detail.data.total') }}</dt>
                        <dd class="font-bold text-emerald-600 text-lg">{{ $toRupiah((int) $data->price) }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('form.labels.checkup_result') }}</dt>
                        <dd class="font-medium text-slate-900">{{ $data->checkup_result ?? '-' }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('patient.record.detail.data.anamnesis') }}</dt>
                        <dd class="font-medium text-slate-900">{{ $data->anamnesis ?? '-' }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">Diagnosis Penyakit (ICD-10)</dt>
                        <dd class="flex flex-col gap-1">
                            @forelse ($diagnosisCodesIcd10 ?? [] as $code)
                                <span class="font-medium text-slate-900 bg-emerald-50 px-2 py-0.5 rounded-lg text-sm inline-flex items-center gap-1">
                                    <span class="text-emerald-600 font-bold">[{{ $code->system }}]</span>
                                    {{ $code->code }} - {{ $code->display }}
                                </span>
                            @empty
                                <span class="text-slate-400">-</span>
                            @endforelse
                        </dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">Tindakan / Prosedur (ICD-9)</dt>
                        <dd class="flex flex-col gap-1">
                            @forelse ($procedureCodesIcd9 ?? [] as $code)
                                <span class="font-medium text-slate-900 bg-teal-50 px-2 py-0.5 rounded-lg text-sm inline-flex items-center gap-1">
                                    <span class="text-teal-600 font-bold">[{{ $code->system }}]</span>
                                    {{ $code->code }} - {{ $code->display }}
                                </span>
                            @empty
                                <span class="text-slate-400">-</span>
                            @endforelse
                        </dd>
                    </div>
                    @if (($otherCodes ?? collect())->isNotEmpty())
                        <div class="preview-container py-2">
                            <dt class="text-sm font-semibold text-slate-600">Kode Lainnya</dt>
                            <dd class="flex flex-col gap-1">
                                @foreach ($otherCodes as $code)
                                    <span class="font-medium text-slate-900 bg-slate-100 px-2 py-0.5 rounded-lg text-sm inline-flex items-center gap-1">
                                        <span class="text-slate-500 font-bold">[{{ $code->system }}]</span>
                                        {{ $code->code }} - {{ $code->display }}
                                    </span>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                </dl>

                <dl class="detail-list">
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('patient.record.detail.data.diagnosis') }} (catatan)</dt>
                        <dd class="font-medium text-slate-900">{{ $data->diagnosis }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('patient.record.detail.data.therapy') }}</dt>
                        <dd class="font-medium text-slate-900">{{ $data->therapy }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('patient.record.detail.data.prescription') }}</dt>
                        <dd class="font-medium text-slate-900">{{ $data->prescription ?? 'No Prescription' }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('patient.record.detail.data.promat') }}</dt>
                        <dd class="font-medium text-slate-900">{{ $data->promat }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('patient.record.detail.data.blood_pressure') }}</dt>
                        <dd class="font-medium text-slate-900">{{ $data->blood_pressure . ' mmHg' }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt class="text-sm font-semibold text-slate-600">{{ __('patient.record.detail.data.cooperativity') }}</dt>
                        <dd class="font-medium text-slate-900">{{ $data->cooperativity }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ __('patient.record.detail.data.service.title') }}</h3>
                <div class="flex flex-col gap-2">
                    @foreach ($data->services ?? [] as $service)
                        <div class="flex flex-col md:flex-row justify-between bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <div class="flex flex-1 flex-col gap-0">
                                <span class="font-semibold text-slate-900">{{ $service->name ?? 'Layanan' }}</span>
                                <span class="text-sm text-slate-500">{{ $service->code ?? '—' }}: {{ $service->category ?? '—' }}</span>
                            </div>
                            <div class="flex flex-col gap-0 text-right">
                                <span class="font-medium text-slate-900">{{ ($service->quantity ?? 1) . ' x ' . $toRupiah($service->price ?? 0) }}</span>
                                <span class="text-sm text-slate-500">Discount: {{ $toRupiah($service->discount ?? 0) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if (isset($data->image_before) && count($data->image_before) > 0)
            <div class="mt-4 pt-4 border-t border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ __('patient.record.detail.data.images.before') }}</h3>
                <div class="grid flex-wrap gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($data->image_before as $index => $image)
                        <img src="{{ asset('storage/' . $image) }}" alt="{{ $index }}" class="rounded-xl border border-slate-200" />
                    @endforeach
                </div>
            </div>
            @endif

            @if (isset($data->image_after) && count($data->image_after) > 0)
            <div class="mt-4 pt-4 border-t border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ __('patient.record.detail.data.images.after') }}</h3>
                <div class="grid flex-wrap gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($data->image_after as $index => $image)
                        <img src="{{ asset('storage/' . $image) }}" alt="{{ $index }}" class="rounded-xl border border-slate-200" />
                    @endforeach
                </div>
            </div>
            @endif
        </section>
    </main>
</x-app-layout>
