<x-app-layout>
    <x-slot:title>{{ __('patient.master.detail.title') }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('patient.master.detail.title') }}</h1>
                <p>{{ $data->name ?? '-' }}</p>
            </div>
        </section>

        <div class="content-card overflow-hidden" x-data="{ tab: 1 }">
            <div class="flex gap-2 p-6 pb-0">
                <button x-on:click="tab = 1"
                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
                    :class="tab === 1 ? 'bg-emerald-500 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    <span class="flex items-center gap-1.5">
                        <x-lucide-user class="w-4 h-4" />
                        {{ __('patient.master.detail.labels.information') }}
                    </span>
                </button>
                <button x-on:click="tab = 2"
                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
                    :class="tab === 2 ? 'bg-emerald-500 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    <span class="flex items-center gap-1.5">
                        <x-lucide-clock class="w-4 h-4" />
                        {{ __('patient.master.detail.labels.history') }}
                    </span>
                </button>
            </div>

            {{-- Tab 1: Information --}}
            <div x-show="tab === 1" class="p-8">
                <section id="information-title" class="mb-6">
                    <span class="font-bold text-lg text-slate-900">{{ __('patient.master.detail.labels.title.information') }}</span>
                </section>

                <section id="personal-data">
                    <dl class="detail-list">
                        <div class="data-container">
                            <dt>{{ __('patient.master.detail.labels.name') }}</dt>
                            <dd class="font-semibold">{{ $data->name ?? '-' }}</dd>
                        </div>

                        <div class="data-container">
                            <dt>{{ __('patient.master.detail.labels.email') }}</dt>
                            <div class="flex flex-col gap-1">
                                @if ($data->payment_email == $data->email || $data->payment_email == '')
                                    <dd class="font-semibold">
                                        {{ $data->email ?? '-' }}
                                        <span class="text-xs text-slate-400 font-normal ml-1">({{ __('patient.master.detail.labels.payment_email') }})</span>
                                    </dd>
                                @else
                                    <dd class="font-semibold">
                                        {{ $data->payment_email ?? '-' }}
                                        <span class="text-xs text-slate-400 font-normal ml-1">({{ __('patient.master.detail.labels.payment_email') }})</span>
                                    </dd>
                                @endif

                                @if (isset($data->payment_email) && $data->payment_email != '')
                                    <dd class="text-sm text-slate-500">{{ $data->email ?? '-' }}</dd>
                                @endif
                            </div>
                        </div>

                        @if (isset($data->sosmed) && (
                            (isset($data->sosmed->facebook) && $data->sosmed->facebook) ||
                            (isset($data->sosmed->instagram) && $data->sosmed->instagram) ||
                            (isset($data->sosmed->tiktok) && $data->sosmed->tiktok) ||
                            (isset($data->sosmed->twitter) && $data->sosmed->twitter)
                        ))
                        <div class="data-container">
                            <dt>{{ __('patient.master.detail.labels.sosmed') }}</dt>
                            <dd class="font-semibold flex flex-col gap-1">
                                @if (isset($data->sosmed->facebook) && $data->sosmed->facebook)
                                    <a href="https://facebook.com/{{ '@' . $data->sosmed->facebook }}" class="flex items-center gap-1.5 text-emerald-600 hover:text-emerald-700">
                                        <x-lucide-facebook class="w-3.5 h-3.5" />
                                        {{ __('patient.master.detail.labels.sosmeds.facebook') }}: {{ $data->sosmed->facebook }}
                                    </a>
                                @endif
                                @if (isset($data->sosmed->instagram) && $data->sosmed->instagram != '')
                                    <a href="https://instagram.com/{{ '@' . $data->sosmed->instagram }}" class="flex items-center gap-1.5 text-emerald-600 hover:text-emerald-700">
                                        <x-lucide-instagram class="w-3.5 h-3.5" />
                                        {{ __('patient.master.detail.labels.sosmeds.instagram') }}: {{ $data->sosmed->instagram }}
                                    </a>
                                @endif
                                @if (isset($data->sosmed->tiktok) && $data->sosmed->tiktok != '')
                                    <a href="https://tiktok.com/{{ '@' . $data->sosmed->tiktok }}" class="flex items-center gap-1.5 text-emerald-600 hover:text-emerald-700">
                                        {{ __('patient.master.detail.labels.sosmeds.tiktok') }}: {{ $data->sosmed->tiktok }}
                                    </a>
                                @endif
                                @if (isset($data->sosmed->twitter) && $data->sosmed->twitter != '')
                                    <a href="https://twitter.com/{{ '@' . $data->sosmed->twitter }}" class="flex items-center gap-1.5 text-emerald-600 hover:text-emerald-700">
                                        <x-lucide-twitter class="w-3.5 h-3.5" />
                                        {{ __('patient.master.detail.labels.sosmeds.twitter') }}: {{ $data->sosmed->twitter }}
                                    </a>
                                @endif
                            </dd>
                        </div>
                        @endif

                        <div class="data-container">
                            <dt>{{ __('patient.master.detail.labels.phone') }}</dt>
                            <dd class="flex gap-2 items-center">
                                <span class="font-medium">{{ $data->phone ?? '-' }}</span>
                                <a href="{{ route('api.followup.whatsapp', ['phone' => $data->phone ?? '8', 'message' => 'Halo, ']) }}"
                                    class="clickable-primary px-3 py-1 text-xs rounded-xl inline-flex items-center gap-1">
                                    <x-lucide-message-circle class="w-3 h-3" />
                                    Follow Up
                                </a>
                            </dd>
                        </div>

                        <div class="data-container">
                            <dt>{{ __('patient.master.detail.labels.birthdate') }}</dt>
                            <dd>{{ $data->birthdate ?? '-' }}</dd>
                        </div>

                        <div class="data-container">
                            <dt>{{ __('patient.master.detail.labels.religion._title') }}</dt>
                            <dd>
                                @switch($data->religion)
                                    @case('ISLAM') {{ __('patient.master.detail.labels.religion.islam') }} @break
                                    @case('CHRISTIANITY') {{ __('patient.master.detail.labels.religion.christianity') }} @break
                                    @case('CATHOLIC') {{ __('patient.master.detail.labels.religion.catholic') }} @break
                                    @case('BUDDHISM') {{ __('patient.master.detail.labels.religion.buddhism') }} @break
                                    @case('HINDUISM') {{ __('patient.master.detail.labels.religion.hinduism') }} @break
                                    @case('KONGHUCHU') {{ __('patient.master.detail.labels.religion.konghuchu') }} @break
                                    @default {{ __('patient.master.detail.labels.religion.other') }}
                                @endswitch
                            </dd>
                        </div>

                        <div class="data-container">
                            <dt>{{ __('patient.master.detail.labels.gender._title') }}</dt>
                            <dd>
                                @switch($data->gender)
                                    @case('MALE') {{ __('patient.master.detail.labels.gender.male') }} @break
                                    @case('FEMALE') {{ __('patient.master.detail.labels.gender.female') }} @break
                                    @default {{ __('patient.master.detail.labels.gender.male') }}
                                @endswitch
                            </dd>
                        </div>

                        <div class="data-container">
                            <dt>{{ __('patient.master.detail.labels.address._title') }}</dt>
                            <dd>{{ __('patient.master.detail.labels.address.string', [
                                'street' => $data->street,
                                'tonarigumi' => $data->tonarigumi,
                                'village' => $data->village,
                                'district' => $data->district,
                                'regency' => $data->regency,
                                'province' => $data->province,
                                'zipcode' => $data->zip_code,
                            ]) }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            {{-- Tab 2: History --}}
            <div x-show="tab === 2" class="p-8">
                <section id="medical-title" class="mb-6">
                    <span class="font-bold text-lg text-slate-900">{{ __('patient.master.detail.labels.title.history') }}</span>
                </section>

                <section id="history-data">
                    @forelse ($data->records as $record)
                        <section class="content-card !bg-slate-50 mb-4 last:mb-0">
                            <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-200">
                                <div>
                                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                        <x-lucide-file-text class="w-4 h-4 text-emerald-500" />
                                        {{ __('Medical Record') }} #{{ strtoupper(substr($record->id, 0, 7)) }}
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ Carbon::parse($record->created_at)->locale('id')->setTimezone('Asia/Jakarta')->isoFormat('dddd, DD MMMM YYYY HH:mm ZZ') }}
                                    </p>
                                </div>
                            </div>

                            <dl class="detail-list">
                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.patient.title') }}</dt>
                                    <dd class="flex flex-col gap-0">
                                        <span class="font-semibold">{{ $record->patient_name }}</span>
                                        <span class="text-sm text-slate-500">{{ __('patient.record.detail.data.patient.id') }}: {{ $record->patient_code }}</span>
                                    </dd>
                                </div>

                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.doctor.title') }}</dt>
                                    <dd class="flex flex-col gap-0">
                                        <span class="font-semibold">{{ $record->doctor_name }}</span>
                                        <span class="text-sm text-slate-500">{{ __('patient.record.detail.data.doctor.nipp') }}: {{ $record->doctor_nipp }}</span>
                                    </dd>
                                </div>

                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.service.title') }}</dt>
                                    <dd class="flex flex-col gap-2">
                                        @foreach ($record->services as $service)
                                            <div class="flex flex-col md:flex-row justify-between bg-white p-3 rounded-xl border border-slate-100">
                                                <div class="flex flex-1 flex-col gap-0">
                                                    <span class="font-semibold">{{ $service->name }}</span>
                                                    <span class="text-sm text-slate-500">{{ $service->code }}: {{ $service->category }}</span>
                                                </div>
                                                <div class="flex flex-col gap-0 text-right">
                                                    <span class="font-medium">{{ $service->quantity . ' x ' . $toRupiah($service->price) }}</span>
                                                    <span class="text-sm text-slate-500">Discount: {{ $toRupiah($service->discount) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </dd>
                                </div>

                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.recomendation') }}</dt>
                                    <dd>
                                        {{ Carbon::parse($record->next_schedule)->locale('id')->setTimezone('Asia/Jakarta')->isoFormat('DD MMMM YYYY') }}
                                    </dd>
                                </div>

                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.total') }}</dt>
                                    <dd class="font-bold text-emerald-600 text-lg">{{ $toRupiah((int) $record->price) }}</dd>
                                </div>

                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('form.labels.checkup_result') }}</dt>
                                    <dd>{{ $record->checkup_result ?? '-' }}</dd>
                                </div>

                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.anamnesis') }}</dt>
                                    <dd>{{ $record->anamnesis ?? '-' }}</dd>
                                </div>

                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.diagnosis') }}</dt>
                                    <dd>{{ $record->diagnosis }}</dd>
                                </div>

                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.therapy') }}</dt>
                                    <dd>{{ $record->therapy }}</dd>
                                </div>

                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.prescription') }}</dt>
                                    <dd>{{ $record->prescription ?? 'No Prescription' }}</dd>
                                </div>

                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.blood_pressure') }}</dt>
                                    <dd>{{ $record->blood_pressure . ' mmHg' }}</dd>
                                </div>

                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.cooperativity') }}</dt>
                                    <dd>{{ $record->cooperativity }}</dd>
                                </div>

                                @if (isset($record->image_before) && count($record->image_before) > 0)
                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.images.before') }}</dt>
                                    <dd class="grid flex-wrap gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                                        @foreach ($record->image_before as $index => $image)
                                            <img src="{{ asset('storage/' . $image) }}" alt="{{ $index }}" class="rounded-xl border border-slate-200" />
                                        @endforeach
                                    </dd>
                                </div>
                                @endif

                                @if (isset($record->image_after) && count($record->image_after) > 0)
                                <div class="preview-container py-2">
                                    <dt class="font-semibold">{{ __('patient.record.detail.data.images.after') }}</dt>
                                    <dd class="grid flex-wrap gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                                        @foreach ($record->image_after as $index => $image)
                                            <img src="{{ asset('storage/' . $image) }}" alt="{{ $index }}" class="rounded-xl border border-slate-200" />
                                        @endforeach
                                    </dd>
                                </div>
                                @endif
                            </dl>
                        </section>
                    @empty
                        <div class="text-center py-12">
                            <x-lucide-folder-open class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                            <p class="text-sm text-slate-500">No medical records found</p>
                        </div>
                    @endforelse
                </section>
            </div>
        </div>
    </main>
</x-app-layout>
