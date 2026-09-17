<x-app-layout>
    <x-slot:title>{{ $type == 'update' ? __('form.title.update.appointment') : __('form.title.create.appointment') }}</x-slot:title>

    <main class="mb-auto px-8 pt-8 pb-12">
        <div class="flex gap-4 items-center">
            <a href="{{ route('appointments.index') }}" class="clickable-ghost w-8 h-8 rounded-md">
                <x-lucide-chevron-left class="w-full h-full" />
            </a>
            <h1>
                {{ $type == 'update' ? __('form.title.update.appointment') : __('form.title.create.appointment') }}
            </h1>
        </div>

        <x-flash-alerts />

        <form method="post" enctype="multipart/form-data" action="{{ $action }}">
            <div class="content-card">
                @csrf
                @if ($type == 'update')
                    <input type="hidden" name="id" value="{{ $data->id }}" />
                @endif

                @if ($type == 'update')
                    @method('put')
                @endif

                <div class="input-container" x-data="patientDataState">
                    @if ($type != 'update')
                        <div class="input-group">
                            <label for="patient_code">{{ __('form.labels.patient_keyword') }}</label>
                            <div class="flex  flex-col md:flex-row gap-2 items-start">
                                <input type="text" name="patient_code" id="patient_code" class="w-full"
                                    placeholder="{{ __('form.placeholders.keyword') }}"
                                    value="{{ $data->patient_code ?? (old('patient_code') ?? '') }}"
                                    x-model="patientKeyword" @keydown="handleKeyDown()" required />
                                <button class="clickable-primary px-4 py-2 rounded-md" @click.prevent="getPatientData()"
                                    id="check_patient">{{ __('form.actions.check') }}</button>
                            </div>
                            <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
                            @error('patient_code')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>
                    @endif

                    <input type="hidden" name="patient_id" id="patient_id" x-model="patientID" />

                    <template x-if="isShown">
                        <table class="text-sm">
                            <thead>
                                <tr>
                                    <th class="py-2">{{ __('form.labels.patient_data') }}</th>
                                    <th class="py-2">{{ __('form.labels.patient_phone') }}</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>

                            <tbody>
                                <template x-for="patient in patientList">
                                    <tr>
                                        <td class="flex flex-col pl-4 py-2">
                                            <span x-text="patient.patient_code"></span>
                                            <span x-text="patient.name"></span>
                                        </td>
                                        <td x-text="patient.phone" class="py-2"></td>
                                        <td>
                                            <template x-if="patientID != patient.id && patientID == ''">
                                                <button class="clickable-primary py-2 px-4 rounded-md"
                                                    @click.prevent="handleSelectPatient(patient.id)">{{ __('select') }}</button>
                                            </template>
                                            <template x-if="patientID != '' && patientID == patient.id">
                                                <button class="clickable-primary py-2 px-4 rounded-md"
                                                    disabled>{{ __('form.actions.selected') }}</button>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </template>

                    <div class="input-group">
                        <label for="doctor_id">{{ __('form.labels.doctor') }}</label>
                        <select name="doctor_id" id="doctor_id" class="selectable">
                            <option value="" disabled selected>
                                {{ __('form.placeholders.doctor') }}
                            </option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}"
                                    {{ isset($data->doctor_id) && $data->doctor_id == $doctor->id ? 'selected' : '' }}
                                    @if (old('doctor_id') == $doctor->id) selected @endif>
                                    {{ $doctor->nipp . ' - ' . $doctor->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <small class="danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label>{{ __('form.labels.service') }}</label>
                        <div id="service-container" class="flex flex-col gap-2 w-full" x-init="$watch('serviceIDList', unmountAddService)">
                            <template x-for="(serviceID, index) in serviceIDList">
                                <div class="flex gap-2">
                                    <select name="service_id[]" :id="`service-${index}`" class="selectable">
                                        <option value="" disabled selected
                                            x-text="`{{ __('form.placeholders.service_plan') }}`">
                                        </option>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}"
                                                :selected="serviceID == '{{ $service->id }}'"
                                                x-text="`{{ $service->code . ' - ' . $service->name }}`">
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="clickable-ghost !border-danger-500 px-2 rounded-md stroke-danger-500"
                                        @click.prevent="handleRemoveService(index)">
                                        <div class="w-6 h-6">
                                            <x-lucide-trash-2 class="w-6 h-6" />
                                        </div>
                                    </button>
                                </div>
                                @error('service_id[]')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </template>
                        </div>
                    </div>

                    <div class="input-group items-start">
                        <button class="clickable-primary px-4 py-2 rounded-md"
                            @click.prevent="handleAddService()">{{ __('general.appointment.form.button.add_service') }}</button>
                    </div>

                    <div class="input-group">
                        <label for="date" class="input-label">{{ __('form.labels.date') }}</label>
                        <input type="date" name="date" id="date" class="input-text"
                            value="{{ $data->date ?? (old('date') ?? Carbon::now()->format('Y-m-d')) }}" />
                        @error('date')
                            <small class="danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="flex flex-col md:flex-row gap-2">
                        <div class="flex-1">
                            <div class="input-group">
                                <label for="start_time" class="input-label">{{ __('form.labels.start_time') }}</label>
                                <input type="time" name="start_time" id="start_time" class="input-text"
                                    value="{{ isset($data->time_start) && $data->time_start ? \Carbon\Carbon::parse($data->time_start)->format('H:i') : (old('start_time') ? date('H:i', strtotime(old('start_time'))) : '') }}" />
                                @error('start_time')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="flex-1">
                            <div class="input-group">
                                <label for="end_time" class="input-label">{{ __('form.labels.end_time') }}</label>
                                <input type="time" name="end_time" id="end_time" class="input-text"
                                    value="{{ isset($data->time_end) && $data->time_end ? \Carbon\Carbon::parse($data->time_end)->format('H:i') : (old('end_time') ? date('H:i', strtotime(old('end_time'))) : '') }}" />
                                @error('end_time')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <input type="submit"
                    value="{{ $type == 'update' ? __('form.actions.update') : __('form.actions.save') }}" id="submit"
                    class="clickable-primary py-2 px-4 mt-4 rounded-md w-full" />
            </div>
        </form>
    </main>


@pushOnce('scripts')
    <script type="text/javascript">
        const patientDataState = {
            patientKeyword: "",
            patientID: "",
            patientList: [],
            serviceIDList: [""],
            isShown: false,
            init() {
                this.patientID = "{{ $data->patient_id ?? old('patient_id') }}";
                this.serviceIDList = @json($data->services ?? old('service_id')) ?? [""];
                if (this.patientID == "" || !this.serviceIDList || typeof this.serviceIDList[0] != "string") return;
                this.serviceIDList = JSON.parse(this.serviceIDList).map(s => s.id);
                this.patientKeyword = this.patientID;
                this.getPatientData();
            },
            handleSelectPatient(patientID) {
                this.patientID = patientID;
                this.patientList = this.patientList.filter((p) => p.id === this.patientID);
            },
            handleKeyDown() {
                this.isShown = false;
                this.patientList = [];
                this.patientID = "";
            },
            getPatientData() {
                const paramsString = new URLSearchParams({
                    search: this.patientKeyword
                }).toString();
                fetch("{{ route('api.appointments.get_patient') }}?" + paramsString, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        method: "GET"
                    })
                    .then((res) => res.json())
                    .then((data) => {
                        this.isShown = false;
                        this.patientList = [];
                        if (!data.data.length) return;

                        this.patientList = data.data;
                        this.isShown = true;
                    });
            },
            handleAddService() {
                this.serviceIDList.push("");
            },
            handleSelectService(index, event) {
                this.serviceIDList[index] = event.target.value;
            },
            handleRemoveService(index) {
                this.serviceIDList.splice(index, 1);
            },
            unmountAddService() {
                const latest = document.querySelector('#service-container *:last-child select');
                initSelectable(latest);
            }
        };
    </script>
@endPushOnce
</x-app-layout>
