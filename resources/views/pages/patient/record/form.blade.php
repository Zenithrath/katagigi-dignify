<x-app-layout>
    <x-slot:title>{{ $type == 'update' ? __('form.title.update.medical_record') : __('form.title.create.medical_record') }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ $type == 'update' ? __('form.title.update.medical_record') : __('form.title.create.medical_record') }}</h1>
                <p>{{ $type == 'update' ? 'Update medical record' : 'Create a new medical record' }}</p>
            </div>
        </section>

        <x-flash-alerts />

        <section id="form-body" x-data="recordState" x-init="$watch('isDataShown', unmountChangeAppointment)">
            <form method="post" action="{{ $action }}" enctype="multipart/form-data">
                @csrf

                @if ($type == 'update')
                    @method('put')
                @endif

                <div class="content-card">
                    <div class="input-group">
                        <label for="appointment_id">{{ __('form.labels.appointment') }}</label>
                        <div class="flex flex-col md:flex-row md:justify-center gap-4 md:gap-2">
                            <select name="appointment_id" id="appointment_id" class="custom-select selectable flex-1">
                                <option value="" selected disabled>
                                    {{ __('form.placeholders.select_appointment') }}</option>
                                @foreach ($appointments as $appointment)
                                    <option value="{{ $appointment->id }}"
                                        {{ $appointment->id == $record->appointment_id ? 'selected' : '' }}>
                                        {{ sprintf('%s - %s (%s %s-%s)', $appointment->patient_code, $appointment->patient_name, $appointment->date, \Carbon\Carbon::parse($appointment->time_start)->format('H:i'), \Carbon\Carbon::parse($appointment->time_end)->format('H:i')) }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="button" class="btn-submit !w-auto !px-6"
                                @click.prevent="handleAppointmentChange()">
                                Find
                            </button>
                        </div>

                        @error('appointment_id')
                            <small class="danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <template x-if="isDataShown">
                        <div id="client-detail" class="my-4 p-4 bg-slate-50 rounded-xl border border-slate-200" x-show="isDataShown">
                            <dl class="detail-list">
                                <div class="preview-container py-2">
                                    <dt class="text-sm font-semibold text-slate-600">{{ __('form.labels.patient_data') }}</dt>
                                    <dd class="font-medium text-slate-900" x-text="patientData"></dd>
                                </div>
                                <div class="preview-container py-2">
                                    <dt class="text-sm font-semibold text-slate-600">{{ __('form.labels.doctor_nipp') }}</dt>
                                    <dd class="font-medium text-slate-900" x-text="doctorNIPP"></dd>
                                </div>
                                <div class="preview-container py-2">
                                    <dt class="text-sm font-semibold text-slate-600">{{ __('form.labels.doctor_name') }}</dt>
                                    <dd class="font-medium text-slate-900" x-text="doctorName"></dd>
                                </div>
                            </dl>
                        </div>
                    </template>

                    <template x-if="isDataShown">
                        <div class="input-group">
                            <label>{{ __('form.labels.service') }}</label>
                            <div id="service-container" class="flex flex-col gap-3" x-init="$watch('selectedServices.length', unmountAddService)">
                                <template x-for="(service, index) in selectedServices">
                                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                        <div class="flex flex-col md:flex-row gap-2">
                                            <select name="service_id[]" :id="`service-${index}`" class="flex-1 custom-select selectable"
                                                @change="handleServiceChange(index, event)">
                                                <option value="" disabled selected>
                                                    {{ __('form.placeholders.service') }}
                                                </option>
                                                <template x-for="item in services">
                                                    <option :value="item.id" x-text="item.code + ' - ' + item.name"
                                                        :selected="item.id == service.id"></option>
                                                </template>
                                            </select>
                                            <input type="number" name="service_quantity[]"
                                                :id="`service-quantity-${index}`" class="custom-input !w-28"
                                                @change="handleQuantityChange(index, event)" required min="1"
                                                placeholder="{{ __('Quantity') }}" x-model="quantityList[index]" />
                                            <input type="number" name="service_price[]" :id="`service-price-${index}`"
                                                :min="service.lower_price" :max="service.upper_price" class="custom-input !w-48"
                                                @change="handlePricingChange(index, event)" required
                                                placeholder="{{ __('Harga Satuan') }}" x-model="priceList[index]" />
                                            <div class="flex gap-2 w-full md:w-64">
                                                <input type="number" name="service_discount[]"
                                                    :id="`service-discount-${index}`" :max="service.upper_price"
                                                    class="custom-input !w-64" min="0"
                                                    @change="handleDiscountChange(index, event)"
                                                    x-model="discountList[index]"
                                                    placeholder="{{ __('form.placeholders.discount') }}" />
                                                <button
                                                    class="clickable-ghost !border-red-300 !text-red-500 px-2 rounded-xl"
                                                    @click.prevent="handleRemoveService(index)">
                                                    <x-lucide-trash-2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                        <small class="helper mt-1" :id="`service-helper-${index}`"
                                            x-text="service.helper_text"></small>
                                        @error('service_id[]')
                                            <small class="danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="isDataShown">
                        <div class="input-group items-start mt-4">
                            <button class="btn-submit !w-auto !px-5"
                                @click.prevent="handleAddService()">
                                <x-lucide-plus class="w-4 h-4" />
                                {{ __('patient.record.form.buttons.add_service') }}
                            </button>
                        </div>
                    </template>

                    <template x-if="isDataShown">
                        <div class="mt-6 pt-6 border-t border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                            <div class="input-group md:col-span-2">
                                <label for="anamnesis">{{ __('form.labels.anamnesis') }}</label>
                                <textarea name="anamnesis" id="anamnesis" cols="30" rows="4" class="custom-input"
                                    placeholder="{{ __('form.placeholders.anamnesis') }}">{{ $record->anamnesis ?? (old('anamnesis') ?? '') }}</textarea>
                                @error('anamnesis')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="input-group md:col-span-2">
                                <label for="checkup_result">{{ __('form.labels.checkup_result') }}</label>
                                <textarea name="checkup_result" id="checkup_result" cols="30" rows="4" class="custom-input"
                                    placeholder="{{ __('form.placeholders.checkup_result') }}">{{ $record->checkup_result ?? (old('checkup_result') ?? '') }}</textarea>
                                @error('checkup_result')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="input-group md:col-span-2">
                                <label>Kode Diagnosis Resmi <span class="text-red-500">*</span></label>
                                <livewire:diagnosis-search :selected="$diagnosisCodes ?? []" />
                                <small class="helper">Wajib pilih minimal 1 kode (ICD-10 / ICD-9 / SNOMED). Ketik bahasa awam, mis. "gigi berlubang".</small>
                            </div>

                            <div class="input-group md:col-span-2">
                                <label for="diagnosis">{{ __('form.labels.diagnosis') }} (catatan tambahan)</label>
                                <textarea name="diagnosis" id="diagnosis" cols="30" rows="4" class="custom-input"
                                    placeholder="{{ __('form.placeholders.diagnosis') }}">{{ $record->diagnosis ?? (old('diagnosis') ?? '') }}</textarea>
                                @error('diagnosis')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="input-group md:col-span-2">
                                <label for="therapy">{{ __('form.labels.therapy') }}</label>
                                <textarea name="therapy" id="therapy" cols="30" rows="4" class="custom-input"
                                    placeholder="{{ __('form.placeholders.therapy') }}">{{ $record->therapy ?? (old('therapy') ?? '') }}</textarea>
                                @error('therapy')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="input-group md:col-span-2">
                                <label for="prescription">{{ __('form.labels.prescription') }}</label>
                                <textarea name="prescription" id="prescription" cols="30" rows="4" class="custom-input"
                                    placeholder="{{ __('form.placeholders.prescription') }}">{{ $record->prescription ?? (old('prescription') ?? '') }}</textarea>
                                @error('prescription')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </template>

                    <template x-if="isDataShown">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1 mt-4">
                            <div class="input-group">
                                <label for="promat">{{ __('form.labels.promat') }}</label>
                                <select name="promat" id="promat" class="custom-select">
                                    <option value="PROMAT" @selected($record->promat === 'PROMAT')>Promat</option>
                                    <option value="NO PROMAT" @selected($record->promat === 'NO PROMAT')>No Promat</option>
                                </select>
                                @error('promat')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="input-group">
                                <label for="blood_pressure">{{ __('form.labels.blood_pressure') }}</label>
                                <input type="text" name="blood_pressure" id="blood_pressure" class="custom-input"
                                    value="{{ $record->blood_pressure ?? '' }}"
                                    placeholder="{{ __('form.placeholders.blood_pressure') }}" />
                                @error('blood_pressure')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="input-group">
                                <label for="cooperativity">{{ __('form.labels.cooperativity') }}</label>
                                <select name="cooperativity" id="cooperativity" class="custom-select">
                                    <option value="COOPERATIVE" @selected($record->cooperativity === 'COOPERATIVE')>
                                        {{ __('patient.record.form.select.cooperativity.cooperative') }}</option>
                                    <option value="LESS COOPERATIVE" @selected($record->cooperativity === 'LESS COOPERATIVE')>
                                        {{ __('patient.record.form.select.cooperativity.less_cooperative') }}</option>
                                    <option value="NOT COOPERATIVE" @selected($record->cooperativity === 'NOT COOPERATIVE')>
                                        {{ __('patient.record.form.select.cooperativity.not_cooperative') }}</option>
                                </select>
                                @error('cooperativity')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1 mt-4 pt-4 border-t border-slate-200">
                        <div class="input-group">
                            <label for="price">{{ __('form.labels.total_price') }}</label>
                            <input type="number" name="price" id="price" x-model="currentPrice" class="custom-input"
                                placeholder="{{ __('form.placeholders.price') }}"
                                value="{{ old('price') ?? ($record->price ?? 0) }}" readonly />
                            @error('price')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="input-group">
                            <label for="discount">{{ __('Discount') }}</label>
                            <input type="number" name="discount" id="discount" x-model="currentDiscount" class="custom-input"
                                value="{{ old('discount') }}" readonly />
                            @error('discount')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="input-group">
                            <label for="billing">{{ __('Total') }}</label>
                            <input type="number" name="billing" id="billing" x-model="billing" class="custom-input"
                                value="{{ old('billing') ?? ($record->billing ?? 0) }}" readonly />
                            @error('billing')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <template x-if="isDataShown">
                        <div class="input-group mt-4">
                            <label for="next_schedule">{{ __('form.labels.next_schedule') }}</label>
                            <input type="date" name="next_schedule" id="next_schedule" class="custom-input"
                                value="{{ $record->next_schedule ? date('Y-m-d', strtotime($record->next_schedule)) : 0 }}" />
                            @error('next_schedule')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </template>

                    <template x-if="isDataShown">
                        <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 pt-4 border-t border-slate-200">
                            <div class="input-group">
                                <label class="text-sm font-semibold text-slate-700">{{ __('form.labels.image_before') }}</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(image, index) in imageBeforeList">
                                        <label :for="`image_before[${index}]`"
                                            class="relative w-20 h-20 border-2 border-dashed border-slate-300 rounded-xl overflow-hidden cursor-pointer hover:border-emerald-400 transition-colors">
                                            <template x-if="imageBeforeList[index].src == ''">
                                                <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-medium">
                                                    + {{ __('form.actions.add') }}
                                                </div>
                                            </template>
                                            <template x-if="imageBeforeList[index].src != ''">
                                                <img :id="`image_before_preview[${index}]`"
                                                    :src="imageBeforeList[index].src" alt=""
                                                    class="w-full h-full object-cover object-center" />
                                            </template>
                                            <input type="hidden" :name="`image_before_meta[${index}]`"
                                                :id="`image_before_meta[${index}]`"
                                                x-model="imageBeforeList[index].src" />
                                            <input type="file" :name="`image_before[${index}]`"
                                                @change="handleImageBeforeChange(index, event)"
                                                :id="`image_before[${index}]`" class="absolute -top-full" />
                                        </label>
                                    </template>
                                </div>
                                @error('image_before')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                                @error('image_before.*')
                                    <small class="danger">
                                        {{ preg_replace('/image_before\.\d+/', 'image submission', $message) }}
                                    </small>
                                @enderror
                                @error('image_before_meta')
                                    <small class="danger">
                                        {{ preg_replace('/image before meta/', 'image submission', $message) }}
                                    </small>
                                @enderror
                                @error('image_before_meta.*')
                                    <small class="danger">
                                        {{ preg_replace('/image_before_meta\.\d+/', 'image submission', $message) }}
                                    </small>
                                @enderror
                            </div>
                            <div class="input-group">
                                <label class="text-sm font-semibold text-slate-700">{{ __('form.labels.image_after') }}</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(image, index) in imageAfterList">
                                        <label :for="`image_after[${index}]`"
                                            class="relative w-20 h-20 border-2 border-dashed border-slate-300 rounded-xl overflow-hidden cursor-pointer hover:border-emerald-400 transition-colors">
                                            <template x-if="imageAfterList[index].src == ''">
                                                <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-medium">
                                                    + {{ __('form.actions.add') }}
                                                </div>
                                            </template>
                                            <template x-if="imageAfterList[index].src != ''">
                                                <img :id="`image_after_preview[${index}]`"
                                                    :src="imageAfterList[index].src" alt=""
                                                    class="w-full h-full object-cover object-center" />
                                            </template>
                                            <input type="hidden" :name="`image_after_meta[${index}]`"
                                                :id="`image_after_meta[${index}]`" x-model="imageAfterList[index].src" />
                                            <input type="file" :name="`image_after[${index}]`"
                                                @change="handleImageAfterChange(index, event)"
                                                :id="`image_after[${index}]`" class="absolute -top-full" />
                                        </label>
                                    </template>
                                </div>
                                @error('image_after')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                                @error('image_after.*')
                                    <small class="danger">
                                        {{ preg_replace('/image_after\.\d+/', 'image submission', $message) }}
                                    </small>
                                @enderror
                                @error('image_after_meta')
                                    <small class="danger">
                                        {{ preg_replace('/image after meta/', 'image submission', $message) }}
                                    </small>
                                @enderror
                                @error('image_after_meta.*')
                                    <small class="danger">
                                        {{ preg_replace('/image_after_meta\.\d+/', 'image submission', $message) }}
                                    </small>
                                @enderror
                            </div>
                        </div>
                    </template>

                    <template x-if="isDataShown">
                        <div class="mt-6 pt-6 border-t border-slate-200 flex justify-end">
                            <button type="submit" class="btn-submit !w-auto !px-8">
                                {{ $type == 'update' ? __('form.actions.update') : __('form.actions.add') }}
                            </button>
                        </div>
                    </template>
                </div>
            </form>
        </section>
    </main>


@pushOnce('scripts')
    <script type="text/javascript">
        const recordState = {
            appointments: [],
            services: [],
            selectedAppointmentID: "",
            isDataShown: false,
            patientData: "",
            doctorNIPP: "",
            doctorName: "",
            selectedServices: [],
            serviceID: "",
            priceList: [],
            quantityList: [],
            discountList: [],
            imageBeforeList: [],
            imageAfterList: [],
            currentPrice: 0,
            currentDiscount: 0,
            billing: 0,
            init: function() {
                let appointmentElement = document.getElementById('appointment_id');
                this.appointments = JSON.parse(@json(json_encode($appointments)));
                this.services = JSON.parse(@json(json_encode($services)));
                this.selectedAppointmentID = appointmentElement.value;
                this.isDataShown = this.selectedAppointmentID;
                this.imageBeforeList = [{
                    src: ""
                }];
                this.imageAfterList = [{
                    src: ""
                }];

                this.loadExsistingValues();
                this.findAppointment();
            },
            loadExsistingValues: function() {
                if ("{{ $type }}" === "create") return;
                const data = @json($record);
                this.selectedServices = data.services;
                this.selectedAppointmentID = data.appointment_id;
                this.patientData = `${data.patient_code} - ${data.patient_name}`
                this.doctorNIPP = data.doctor_nipp;
                this.doctorName = data.doctor_name;
                this.priceList = data.services.map((s) => s.price);
                this.discountList = data.services.map((s) => s.discount);
                this.quantityList = data.services.map((s) => s.quantity);
                this.currentPrice = data.price;
                this.currentDiscount = data.discount;
                this.billing = data.billing;
                this.imageAfterList = [];
                this.imageBeforeList = [];
                data.image_after.map((item, index) => this.imageAfterList.push({
                    src: `/storage/${item}`
                }));
                data.image_before.map((item) => this.imageBeforeList.push({
                    src: `/storage/${item}`
                }));

                this.imageAfterList.push({
                    src: ""
                });
                this.imageBeforeList.push({
                    src: ""
                });
            },
            findAppointment: function() {
                if ("{{ $type }}" === "update") return;
                const appointment = this.appointments.find((a) => a.id === this.selectedAppointmentID);

                if (!appointment) return;
                this.patientData = `${appointment.patient_code} - ${appointment.patient_name}`;
                this.doctorNIPP = appointment.doctor_nipp;
                this.doctorName = appointment.doctor_name;
                this.selectedServices = appointment.services.map((s) => {
                    const selectedService = this.services.find((ss) => s.id === ss.id);
                    return {
                        id: s.id ?? "",
                        lower_price: selectedService.lower_price ?? 0,
                        upper_price: selectedService.upper_price ?? 0,
                        helper_text: `{{ __('form.helpers.pricing', ['lower' => 'lower', 'higher' => 'higher']) }}`
                            .replace('lower', convertRupiah(Number(selectedService.lower_price)))
                            .replace('higher', convertRupiah(Number(selectedService.upper_price))) ??
                            `{{ __('form.helpers.service') }}`,
                    };
                });

                this.isDataShown = Boolean(appointment);
            },
            handleAppointmentChange() {
                this.selectedAppointmentID = document.getElementById('appointment_id').value;
                this.findAppointment();
            },
            handleAddService() {
                this.selectedServices.push({
                    id: "",
                    lower_price: 0,
                    upper_price: 0,
                    helper_text: `{{ __('form.helpers.service') }}`,
                });
            },
            handleRemoveService(index) {
                this.billing = this.billing - ((this.priceList[index] * this.quantityList[index]) - this
                    .discountList[index]);
                this.currentDiscount -= this.discountList[index];
                this.currentPrice -= this.priceList[index] * this.quantityList[index];
                this.priceList.splice(index, 1);
                this.discountList.splice(index, 1);
                this.selectedServices.splice(index, 1);
            },
            handleServiceChange(index, event) {
                const service = this.services.find(s => s.id == event.target.value);
                const data = {
                    id: service.id ?? "",
                    lower_price: service.lower_price ?? 0,
                    upper_price: service.upper_price ?? 0,
                    helper_text: `{{ __('form.helpers.pricing', ['lower' => 'lower', 'higher' => 'higher']) }}`
                        .replace('lower', convertRupiah(Number(service.lower_price)))
                        .replace('higher', convertRupiah(Number(service.upper_price))) ??
                        `{{ __('form.helpers.service') }}`,
                };

                const forceChange = function() {
                    const price = document.getElementById(`service-price-${index}`);
                    price.setAttribute('min', data.lower_price);
                    price.setAttribute('max', data.upper_price);
                    const discount = document.getElementById(`service-discount-${index}`);
                    discount.setAttribute('max', data.upper_price);
                    const helper = document.getElementById(`service-helper-${index}`);
                    helper.innerText = data.helper_text;
                }

                this.selectedServices[index] = data;
                forceChange();
            },
            handleImageBeforeChange(index, event) {
                if (event.target.files.length <= 0) return;
                let src = URL.createObjectURL(event.target.files[0]);
                this.imageBeforeList[index].src = src;

                if (index != this.imageBeforeList.length - 1) return;
                this.imageBeforeList.push({
                    src: ""
                });
            },
            handleImageAfterChange(index, event) {
                if (event.target.files.length <= 0) return;
                let src = URL.createObjectURL(event.target.files[0]);
                this.imageAfterList[index].src = src;

                if (index != this.imageAfterList.length - 1) return;
                this.imageAfterList.push({
                    src: ""
                });
            },
            calculatePricing() {
                this.currentPrice = 0;
                this.currentDiscount = 0;
                this.billing = 0;
                this.selectedServices.forEach((_, index) => {
                    const subtotal = Number(this.priceList[index] ?? 0) * Number(this.quantityList[index] ??
                        0);
                    this.currentPrice += subtotal;
                    if (subtotal > 0) {
                        this.currentDiscount += Number(this.discountList[index] ?? 0);
                    }
                    this.billing = this.currentPrice - this.currentDiscount;
                });
            },
            handlePricingChange(index, event) {
                this.priceList[index] = event.target.value;
                this.calculatePricing();
            },
            handleDiscountChange(index, event) {
                this.discountList[index] = event.target.value;
                this.calculatePricing();
            },
            handleQuantityChange(index, event) {
                this.quantityList[index] = event.target.value;
                const discount = document.getElementById(`service-discount-${index}`);
                discount.setAttribute('max', this.selectedServices[index].upper_price * event.target.value);
                this.calculatePricing();
            },
            unmountAddService() {
                const latest = document.querySelector('#service-container *:last-child select');
                const self = recordState;
                const handler = function(event, callback) {
                    return function() {
                        return callback(event);
                    }
                }

                initSelectable(latest);
                $(latest).on('select2:select', (event) => handler(event, function(event) {
                    const index = latest.getAttribute('id').split('-')[1];
                    self.handleServiceChange(index, event);
                })())
            },
            unmountChangeAppointment() {
                const selects = document.querySelectorAll('select');
                const self = recordState;
                const handler = function(event, callback) {
                    return function() {
                        return callback(event);
                    }
                }

                selects.forEach((select) => {
                    initSelectable(select);

                    if (select.getAttribute('id').match('^service-[0-9]+')) {
                        $(select).on('select2:select', (event) => handler(event, function(event) {
                            const index = select.getAttribute('id').split('-')[1];
                            self.handleServiceChange(index, event);
                        })())
                    };
                });
            }
        };
    </script>
@endPushOnce
</x-app-layout>
