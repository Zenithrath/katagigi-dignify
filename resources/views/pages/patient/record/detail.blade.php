<x-app-layout>
    <x-slot:title>{{ __('patient.record.detail.index._title') }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div class="flex items-start gap-4">
                <a href="{{ route('medical-records.index') }}" class="clickable-ghost w-9 h-9 rounded-xl p-2">
                    <x-lucide-chevron-left class="w-full h-full" />
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ __('Medical Record') }} #{{ strtoupper(substr($data->id, 0, 7)) }}</h1>
                    <p>{{ Carbon::parse($data->created_at)->locale('id')->setTimezone('Asia/Jakarta')->isoFormat('dddd, DD MMMM YYYY HH:mm ZZ') }}
                    </p>
                </div>
            </div>
        </section>

        <section class="content-card">
            <dl class="detail-list">
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.patient.title') }}</dt>
                    <dd class="flex flex-col gap-0">
                        <span class="font-semibold">{{ $data->patient_name }}</span>
                        <span>{{ __('patient.record.detail.data.patient.id') }}: {{ $data->patient_code }}</span>
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.doctor.title') }}</dt>
                    <dd class="flex flex-col gap-0">
                        <span class="font-semibold">{{ $data->doctor_name }}</span>
                        <span>{{ __('patient.record.detail.data.doctor.nipp') }}: {{ $data->doctor_nipp }}</span>
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.service.title') }}</dt>
                    <dd class="flex flex-col gap-2">
                        @foreach ($data->services as $service)
                            <div class="flex flex-col md:flex-row justify-between">
                                <div class="flex flex-1 flex-col gap-0">
                                    <span class="font-semibold">{{ $service->name }}</span>
                                    <span>{{ $service->code }}: {{ $service->category }}</span>
                                </div>
                                <div class="flex flex-col gap-0 text-right">
                                    <span
                                        class="text-semibold">{{ $service->quantity . ' x ' . $toRupiah($service->price) }}</span>
                                    <span class="text-semibold">Discount: {{ $toRupiah($service->discount) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.recomendation') }}</dt>
                    <dd class="flex flex-col gap-0">
                        {{ Carbon::parse($data->next_schedule)->locale('id')->setTimezone('Asia/Jakarta')->isoFormat('DD MMMM YYYY') }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.total') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $toRupiah((int) $data->price) }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('form.labels.checkup_result') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $data->checkup_result ?? '-' }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.anamnesis') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $data->anamnesis ?? '-' }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">Kode Diagnosis Resmi</dt>
                    <dd class="flex flex-col gap-1 font-bold">
                        @forelse ($diagnosisCodes ?? [] as $code)
                            <span>[{{ $code->system }} {{ $code->code }}] {{ $code->display }}</span>
                        @empty
                            <span>-</span>
                        @endforelse
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.diagnosis') }} (catatan)</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $data->diagnosis }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.therapy') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $data->therapy }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.prescription') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $data->prescription ?? 'No Prescription' }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.promat') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $data->promat }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.blood_pressure') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $data->blood_pressure . ' mmHg' }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.cooperativity') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $data->cooperativity }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.images.before') }}</dt>
                    <dd class="grid flex-wrap gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($data->image_before as $index => $image)
                            <img src="{{ asset('storage/' . $image) }}" alt="{{ $index }}" />
                        @endforeach
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('patient.record.detail.data.images.after') }}</dt>
                    <dd class="grid flex-wrap gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($data->image_after as $index => $image)
                            <img src="{{ asset('storage/' . $image) }}" alt="{{ $index }}" />
                        @endforeach
                    </dd>
                </div>
            </dl>
        </section>
    </main>
</x-app-layout>
