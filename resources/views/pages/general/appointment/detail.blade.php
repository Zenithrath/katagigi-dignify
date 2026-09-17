<x-app-layout>
    <x-slot:title>{{ __('general.appointment.detail._title') }}</x-slot:title>

    <main class="main-table-container">
        <div class="flex gap-4 items-center">
            <a href="{{ route('patients.index') }}" class="clickable-ghost w-9 h-9 rounded-xl">
                <x-lucide-chevron-left class="w-full h-full" />
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
                                    {{ sprintf('%s - %s, %s', $service->code, $service->name, $service->category_name) }}
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
                        <dd>{{ Carbon::parse($data->time_start)->format('H:i') . ' - ' . \Carbon\Carbon::parse($data->time_end)->format('H:i') }}
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
    </main>
</x-app-layout>
