<x-app-layout>
    <x-slot:title>{{ __('general.appointment.detail._title') }}</x-slot:title>

    <main class="main-table-container" x-data="{ checkinDrawer: {{ session('checkin_pending') || $errors->has('checkin') ? 'true' : 'false' }} }">
        <div class="flex gap-4 items-center">
            <a href="{{ route('patients.index') }}" class="clickable-ghost w-9 h-9 rounded-xl">
            <x-icon name="lucide-chevron-left" class="w-full h-full" />
            </a>
            <h1 class="text-xl font-bold text-slate-900">{{ __('general.appointment.detail._title') }}</h1>
        </div>

        <div class="content-card pt-8 px-8">
            <section id="patient-data" class="mt-4">
                <h3 class="font-semibold text-lg mb-2">{{ __('general.appointment.detail.data.patient') }}</h3>
                <dl class="detail-list">
                    <div class="preview-container py-2">
                        <dt>{{ __('general.appointment.detail.labels.patient.name') }}</dt>
                        <dd>{{ $data->patient_name ?? 'patient phone' }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt>{{ __('general.appointment.detail.labels.patient.mr_number') }}</dt>
                        <dd>{{ $data->patient_code ?? 'patient phone' }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt>{{ __('general.appointment.detail.labels.patient.phone') }}</dt>
                        <dd>{{ $data->patient_phone ?? 'patient phone' }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt>{{ __('general.appointment.detail.labels.patient.address') }}</dt>
                        <dd>{{ __('general.appointment.detail.labels.patient.address_details', [
                            'street' => $data->patient_street,
                            'tonarigumi' => $data->patient_tonarigumi,
                            'village' => $data->patient_village,
                            'district' => $data->patient_district,
                            'regency' => $data->patient_regency,
                            'province' => $data->patient_province,
                            'zipcode' => $data->patient_zip_code,
                        ]) }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section id="doctor-data" class="mt-4">
                <h3 class="font-semibold text-lg mb-2">{{ __('general.appointment.detail.data.doctor') }}</h3>
                <dl class="detail-list">
                    <div class="preview-container py-2">
                        <dt>{{ __('general.appointment.detail.labels.doctor.name') }}</dt>
                        <dd>{{ $data->doctor_name ?? 'patient phone' }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt>{{ __('general.appointment.detail.labels.doctor.nipp') }}</dt>
                        <dd>{{ $data->doctor_nipp ?? 'patient phone' }}</dd>
                    </div>
                </dl>
            </section>

            <section id="schedule-data" class="mt-4">
                <h3 class="font-semibold text-lg mb-2">{{ __('general.appointment.detail.data.schedule') }}</h3>
                <dl class="detail-list">
                    <div class="preview-container py-2">
                        <dt>{{ __('general.appointment.detail.labels.schedule.service_name') }}</dt>
                        <dd class="flex flex-col gap-2">
                            @foreach ($data->services as $service)
                                <div class="">
                                    {{ sprintf('%s - %s, %s', $service->code ?? '-', $service->name ?? '-', $service->category_name ?? '-') }}
                                </div>
                            @endforeach
                        </dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt>{{ __('general.appointment.detail.labels.schedule.date') }}</dt>
                        <dd>{{ $data->date ?? 'patient phone' }}</dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt>{{ __('general.appointment.detail.labels.schedule.time') }}</dt>
                        <dd>{{ \Carbon\Carbon::parse($data->time_start)->format('H:i') . ' - ' . \Carbon\Carbon::parse($data->time_end)->format('H:i') }}
                        </dd>
                    </div>
                    <div class="preview-container py-2">
                        <dt>{{ __('general.appointment.detail.labels.schedule.status') }}</dt>
                        <dd class="flex">
                            @if ($data->canceled_at)
                                <small class="px-2 py-0.5 bg-red-100 border border-red-600 text-red-600 rounded-md">
                                    {{ __('general.appointment.index.table.canceled') }}
                                </small>
                            @elseif ($data->confirmed_at)
                                <small
                                    class="px-2 py-0.5 bg-yellow-100 border border-yellow-600 text-yellow-600 rounded-md">
                                    {{ __('general.appointment.index.table.confirmed') }}
                                </small>
                            @elseif ($data->confirmed_at && $data->paid_at && !$data->recorded_at)
                                <small class="px-2 py-0.5 bg-green-100 border border-green-600 text-green-600 rounded-md">
                                    {{ __('general.appointment.index.table.served and paid') }}
                                </small>
                            @elseif ($data->confirmed_at && $data->paid_at && !$data->recorded_at)
                                <small class="px-2 py-0.5 bg-blue-100 border border-blue-600 text-blue-600 rounded-md">
                                    {{ __('general.appointment.index.table.completed') }}
                                </small>
                            @else
                                <small
                                    class="px-2 py-0.5 bg-orange-100 border border-orange-600 text-orange-600 rounded-md">
                                    {{ __('general.appointment.index.table.pending') }}
                                </small>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section id="action" class="mt-4 flex justify-end w-full">
                <div class="flex gap-4 items-center">
                    @if (($visit ?? null))
                        <a href="{{ route('visits.show', $visit->id) }}"
                            class="clickable-primary py-2.5 px-5 rounded-xl">
                            Buka Visit {{ $visit->visit_number }}
                        </a>
                    @elseif (($data->confirmed_at ?? null) && ! ($data->canceled_at ?? null))
                        @can('create visit')
                            <form action="{{ route('appointments.checkin', ['appointment' => $data->id]) }}" method="post">
                                @csrf
                                <button type="submit" class="clickable-primary py-2.5 px-5 rounded-xl">
                                    Check-in
                                </button>
                            </form>
                        @endcan
                    @endif
                    @can('update appointment')
                        <a href="{{ route('appointments.edit', ['appointment' => $data->id]) }}"
                            class="clickable-primary py-2.5 px-5 rounded-xl">
                            {{ __('general.appointment.detail.action.edit') }}
                            <span class="sr-only">{{ $data->patient_name }}</span>
                        </a>
                    @endcan
                    @if ($data->id !== auth()->user()->id)
                        @can('delete appointment')
                            <form action="{{ route('appointments.destroy', ['appointment' => $data->id]) }}" method="post">
                                @csrf
                                @method('delete')
                                <button
                                    class="text-danger-600 hover:text-danger-500 active:text-danger-700 clickable-ghost py-2 px-4 rounded-md"
                                    type="submit">{{ __('general.appointment.detail.action.delete') }}<span
                                        class="sr-only">{{ $data->patient_name }}</span></button>
                            </form>
                        @endcan
                    @endif
                </div>
            </section>
        </div>

        {{-- Drawer pelengkap check-in: data kurang → isi di sini,
             1 klik langsung jadi visit (tanpa ke halaman edit pasien). --}}
        @can('create visit')
            <div x-show="checkinDrawer" class="fixed inset-0 z-40" style="display: none;">
                <div class="absolute inset-0 bg-slate-900/50" x-on:click="checkinDrawer = false"></div>
                <aside class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-xl overflow-y-auto">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                        <div>
                            <p class="text-xs font-semibold text-slate-500">LENGKAPI DATA CHECK-IN</p>
                            <p class="text-base font-bold text-slate-900">{{ $data->patient_name ?? '-' }}</p>
                        </div>
                        <button type="button" x-on:click="checkinDrawer = false" class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100" aria-label="Tutup">
                            <span class="text-lg leading-none">×</span>
                        </button>
                    </div>
                    <form method="post" action="{{ route('appointments.checkin.complete', ['appointment' => $data->id]) }}" class="p-5 space-y-3">
                        @csrf
                        @if ($errors->has('checkin'))
                            <p class="text-sm text-red-600">{{ $errors->first('checkin') }}</p>
                        @endif
                        <p class="text-xs text-slate-500">Kurang: {{ implode(', ', $checkinMissing ?? []) ?: '—' }}</p>
                        <div class="input-group">
                            <label for="ci_nik">NIK (16 digit)</label>
                            <input type="text" name="nik" id="ci_nik" class="custom-input" value="{{ old('nik', $patient->nik ?? '') }}" />
                            @error('nik')<small class="danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="input-group">
                            <label for="ci_phone">No. HP (08…)</label>
                            <input type="text" name="phone" id="ci_phone" class="custom-input" value="{{ old('phone') }}" placeholder="{{ $patient->phone ?? '' }}" />
                            @error('phone')<small class="danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="input-group !mb-0">
                                <label for="ci_birthdate">Tanggal lahir</label>
                                <input type="date" name="birthdate" id="ci_birthdate" class="custom-input" value="{{ old('birthdate', isset($patient->birthdate) ? \Carbon\Carbon::parse($patient->birthdate)->format('Y-m-d') : '') }}" />
                                @error('birthdate')<small class="danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="input-group !mb-0">
                                <label for="ci_gender">Jenis kelamin</label>
                                <select name="gender" id="ci_gender" class="custom-select">
                                    <option value="">— Pilih —</option>
                                    <option value="MALE" @selected(old('gender', $patient->gender ?? '') === 'MALE')>Laki-laki</option>
                                    <option value="FEMALE" @selected(old('gender', $patient->gender ?? '') === 'FEMALE')>Perempuan</option>
                                </select>
                                @error('gender')<small class="danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="input-group !mb-0">
                                <label for="ci_street">Jalan</label>
                                <input type="text" name="street" id="ci_street" class="custom-input" value="{{ old('street', $data->patient_street ?? '') }}" />
                            </div>
                            <div class="input-group !mb-0">
                                <label for="ci_village">Desa/Kelurahan</label>
                                <input type="text" name="village" id="ci_village" class="custom-input" value="{{ old('village', $data->patient_village ?? '') }}" />
                            </div>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="satusehat_consent" value="1" @checked(old('satusehat_consent', $patient->satusehat_consent ?? false)) class="w-4 h-4 rounded border-slate-300 text-emerald-600" />
                            Persetujuan SATUSEHAT
                        </label>
                        <button type="submit" class="btn-submit">Lengkapi &amp; Check-in</button>
                    </form>
                </aside>
            </div>
        @endcan
    </main>
</x-app-layout>
