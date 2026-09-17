<x-app-layout>
    <x-slot:title>{{ __('report.transaction.index._title') }}</x-slot:title>

    <main class="main-table-container">
        <section class="flex gap-4 items-center">
            <a href="{{ route('transactions.index') }}" class="clickable-ghost w-9 h-9 rounded-xl">
                <x-lucide-chevron-left class="w-full h-full" />
            </a>
            <h1 class="text-xl font-bold text-slate-900"> {{ $type == 'update' ? __('form.title.update.transaction') : __('form.title.create.transaction') }}
            </h1>
        </section>

        <x-flash-alerts />

        <section class="content-card" x-data="appointmentState" x-init="$watch('isDataShown', unmountChangeAppointment)">
            <form action="{{ route('transactions.store') }}" method="post">
                @csrf

                <div class="input-container">
                    <div class="input-group">
                        <label for="appointment_id">{{ __('form.labels.appointment') }}</label>
                        <div class="flex flex-col md:flex-row md:justify-center gap-4 md:gap-2">
                            <select name="appointment_id" id="appointment_id" x-model="selectedAppointmentID"
                                class="selectable">
                                <option value="" disabled selected>
                                    {{ __('form.placeholders.select_unpaid_appointment') }}
                                </option>
                                @foreach ($appointments as $appointment)
                                    <option value="{{ $appointment->id }}">
                                        {{ sprintf('%s - %s (%s %s-%s)', $appointment->patient->code, $appointment->patient->name, $appointment->date, $appointment->time_start, $appointment->time_end) }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="button" class="clickable-primary py-2 px-4 rounded-md w-full md:w-fit"
                                @click.prevent="findAppointment()">
                                {{ __('form.actions.find') }}
                            </button>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="voucher_code">{{ __('form.labels.voucher_code') }}</label>
                        <input type="text" name="voucher_code" id="voucher_code"
                            placeholder="{{ __('form.placeholders.voucher_code') }}" />
                    </div>

                    <template x-if="isDataShown">
                        <dl class="detail-list">
                            <div class="preview-container py-2">
                                <dt>{{ __('form.labels.patient_id') }}</dt>
                                <dd x-text="detailData.patient.code"></dd>
                            </div>
                            <div class="preview-container py-2">
                                <dt>{{ __('form.labels.patient_name') }}</dt>
                                <dd x-text="detailData.patient.name"></dd>
                            </div>
                            <div class="preview-container py-2">
                                <dt>{{ __('form.labels.doctor_nipp') }}</dt>
                                <dd x-text="detailData.doctor.nipp"></dd>
                            </div>
                            <div class="preview-container py-2">
                                <dt>{{ __('form.labels.doctor_name') }}</dt>
                                <dd x-text="detailData.doctor.name"></dd>
                            </div>
                            <div class="preview-container py-2">
                                <dt>{{ __('form.labels.appointment_date') }}</dt>
                                <dd x-text="detailData.date"></dd>
                            </div>
                        </dl>
                    </template>

                    <template x-if="isDataShown">
                        <div class="input-group">
                            <label>{{ __('form.labels.service') }}</label>
                            <div id="service-container" x-init="$watch('appointmentServiceList.length', unmountAddService)" class="flex flex-col gap-2 w-full">
                                <template x-for="(service, index) in appointmentServiceList">
                                    <div>
                                        <div class="flex flex-col md:flex-row gap-2">
                                            <select name="service_id[]" :id="`service-${index}`" class="flex-1 selectable"
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
                                                :id="`service-quantity-${index}`" class="w-full md:w-28"
                                                @change="handleQuantityChange(index, event)" required min="1"
                                                placeholder="{{ __('Quantity') }}" />
                                            <input type="number" name="service_price[]" :id="`service-price-${index}`"
                                                :min="service.lower_price" :max="service.upper_price" class="w-full md:w-48"
                                                @change="handlePricingChange(index, event)" required
                                                placeholder="{{ __('Harga Satuan') }}" />
                                            <div class="flex gap-2 md:w-64">
                                                <input type="number" name="service_discount[]"
                                                    :id="`service-discount-${index}`" min="0"
                                                    :max="service.upper_price * quantityList[index]" class="flex-1"
                                                    @change="handleDiscountChange(index, event)"
                                                    placeholder="{{ __('form.placeholders.discount') }}" />
                                                <button type="button"
                                                    class="clickable-ghost !border-danger-500 px-2 rounded-md stroke-danger-500"
                                                    @click.prevent="handleRemoveService(index)">
                                                    <div class="w-6 h-6">
                                                        <x-lucide-trash-2 class="w-6 h-6" />
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="helper" :id="`service-helper-${index}`"
                                            x-text="service.helper_text"></small>
                                    </div>
                                </template>
                                @error('service_id.*')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </template>

                    <template x-if="isDataShown">
                        <div class="input-group items-start mt-0">
                            <button class="clickable-primary px-4 py-2 rounded-md" type="button"
                                @click.prevent="handleAddService()">{{ __('form.actions.add_service') }}</button>
                        </div>
                    </template>

                    <template x-if="isDataShown">
                        <table>
                            <thead>
                                <tr>
                                    <th class="column">{{ __('general.table.number_no') }}</th>
                                    <th class="column">{{ __('form.labels.type') }}</th>
                                    <th class="column">{{ __('form.labels.step') }}</th>
                                    <th class="column">{{ __('form.labels.amount') }}</th>
                                    <th class="column">{{ __('form.labels.due_date') }}</th>
                                    <th class="column">{{ __('form.labels.status') }}</th>
                                    <th class="column">{{ __('form.labels.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in installmentList">
                                    <tr>
                                        <td class="column" x-text="index + 1"></td>
                                        <td class="column" x-text="item.type"></td>
                                        <td class="column" x-text="item.step"></td>
                                        <td class="column" x-text="convertRupiah(item.amount)"></td>
                                        <td class="column" x-text="item.due_date"></td>
                                        <td class="column" x-text="item.status">
                                        </td>
                                        <td class="column">
                                            <label class="relative">
                                                <input type="radio" name="installment_step"
                                                    :id="`installment_step_${index}`" :value="item.id"
                                                    class="absolute hidden peer" />
                                                <div
                                                    class="py-2 px-4 hover:bg-brand-700 hover:text-item-50 peer-checked:text-item-50 peer-checked:bg-brand-800 cursor-pointer peer-checked:cursor-default border border-slate-200 rounded-md basic-transition">
                                                    {{ __('form.labels.pay') }}
                                                </div>
                                            </label>
                                        </td>
                                    </tr>
                                </template>

                                <template x-if="!installmentList.length">
                                    <tr>
                                        <td colspan="7" class="column text-center">
                                            {{ __('general.phrases.no_data') }}
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </template>

                    <template x-if="isDataShown">
                        <div class="input-group">
                            <label for="assistant_id">{{ __('form.labels.assistant') }}</label>
                            <select name="assistant_id" id="assistant_id" class="flex-1 selectable"
                                x-model="assistantID">
                                <option value="" disabled selected>
                                    {{ __('form.placeholders.assistants') }}
                                </option>
                                <template x-for="item in assistants">
                                    <option :value="item.id" x-text="item.nipp + ' - ' + item.name"
                                        :selected="item.id == assistantID"></option>
                                </template>
                            </select>
                            @error('assistant_id')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </template>

                    <template x-if="isDataShown">
                        <div class="input-group">
                            <label for="next_schedule">{{ __('form.labels.next_schedule') }}</label>
                            <input type="date" name="next_schedule" id="next_schedule" />
                        </div>
                    </template>

                    <div class="flex flex-col md:flex-row gap-2 w-full">
                        <div class="input-group flex-1">
                            <label for="price">{{ __('form.labels.total_price') }}</label>
                            <input type="number" name="price" id="price" x-model="currentPrice" readonly />
                        </div>

                        <div class="input-group flex-1">
                            <label for="discount">{{ __('form.labels.discount') }}</label>
                            <input type="number" name="discount" id="discount" x-model="discount" readonly />
                        </div>

                        <div class="input-group flex-1">
                            <label for="billing">{{ __('form.labels.price_after_discount') }}</label>
                            <input type="number" name="billing" id="billing" x-model="billTotal" readonly />
                        </div>
                    </div>

                    <template x-if="isDataShown">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_installment" id="is_installment"
                                x-model="isNewInstallment" />
                            <label for="is_installment">{{ __('form.labels.set_installment') }}</label>
                        </div>
                    </template>

                    <template x-if="isNewInstallment">
                        <div class="flex flex-col md:flex-row gap-2 w-full">
                            <div class="input-group flex-1">
                                <label for="down_payment_amount">{{ __('form.labels.down_payment_amount') }}</label>
                                <input type="number" name="down_payment_amount" id="down_payment_amount"
                                    @keydown="handleInstallmentChange" x-model="installmentSimulation.down_payment" />
                            </div>

                            <div class="input-group flex-1">
                                <label for="installment_period">{{ __('form.labels.installment_period') }}</label>
                                <input type="number" name="installment_period" id="installment_period"
                                    @keydown="handleInstallmentChange"
                                    x-model="installmentSimulation.installment_period" />
                            </div>

                            <div class="input-group flex-1">
                                <label for="installment_amount">{{ __('form.labels.installment_amount') }}</label>
                                <input type="number" name="installment_amount" id="installment_amount" readonly
                                    x-model="installmentSimulation.installment_amount" />
                            </div>
                        </div>
                    </template>

                    <template x-if="isDataShown">
                        <div class="input-group">
                            <span class="text-sm">{{ __('form.labels.payment_method') }}</span>
                            <div class="flex flex-row gap-2">
                                <label class="relative">
                                    <input type="radio" name="payment_method" id="payment_method_transfer"
                                        value="TRANSFER" class="absolute hidden peer" />
                                    <div
                                        class="py-2 px-4 hover:bg-brand-700 hover:text-item-50 peer-checked:text-item-50 peer-checked:bg-brand-800 cursor-pointer peer-checked:cursor-default border border-slate-200 rounded-md basic-transition">
                                        {{ __('form.labels.transfer') }}
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="payment_method" id="payment_method_cash" value="CASH"
                                        class="absolute hidden peer" />
                                    <div
                                        class="py-2 px-4 hover:bg-brand-700 hover:text-item-50 peer-checked:text-item-50 peer-checked:bg-brand-800 cursor-pointer peer-checked:cursor-default border border-slate-200 rounded-md basic-transition">
                                        {{ __('form.labels.cash') }}
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="payment_method" id="payment_method_qris" value="QRIS"
                                        class="absolute hidden peer" />
                                    <div
                                        class="py-2 px-4 hover:bg-brand-700 hover:text-item-50 peer-checked:text-item-50 peer-checked:bg-brand-800 cursor-pointer peer-checked:cursor-default border border-slate-200 rounded-md basic-transition">
                                        {{ __('form.labels.qris') }}
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="payment_method" id="payment_method_owlexa"
                                        value="OWLEXA_INSURANCE" class="absolute hidden peer" />
                                    <div
                                        class="py-2 px-4 hover:bg-brand-700 hover:text-item-50 peer-checked:text-item-50 peer-checked:bg-brand-800 cursor-pointer peer-checked:cursor-default border border-slate-200 rounded-md basic-transition">
                                        {{ __('form.labels.owlexa_insurance') }}
                                    </div>
                                </label>
                            </div>
                            @error('payment_method')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </template>

                    <template x-if="isDataShown">
                        <input type="submit" value="{{ __('form.actions.save_and_print') }}"
                            class="btn-submit" />
                    </template>
                </div>
            </form>
        </section>
    </main>


@pushOnce('scripts')
    <script type="text/javascript">
        const appointmentState = {
            appointments: [],
            appointment: null,
            selectedAppointmentID: "",
            quantityList: [],
            priceList: [],
            currentPrice: 0,
            discountList: [],
            discount: 0,
            billTotal: 0,
            services: [],
            appointmentServiceList: [],
            installmentList: [],
            installmentSimulation: {
                down_payment: 0,
                installment_period: 0,
                installment_amount: 0,
            },
            isDataShown: false,
            isNewInstallment: false,
            assistants: [],
            assistantID: "",
            detailData: {
                patient: {
                    id: "",
                    name: "",
                    code: "",
                },
                doctor: {
                    id: "",
                    name: "",
                    nipp: "",
                },
                date: "",
            },
            init() {
                this.services = JSON.parse(@json(json_encode($services)));
                this.appointments = JSON.parse(@json(json_encode($appointments)));
                this.assistants = JSON.parse(@json(json_encode($assistants)));

                if (this.appointment) {
                    this.services = this.appointment.services;
                }
            },
            setDataFromAppointment(appointment) {
                this.detailData = {
                    patient: {
                        id: appointment.patient?.id ?? "",
                        name: appointment.patient?.name ?? "",
                        code: appointment.patient?.code ?? "",
                    },
                    doctor: {
                        id: appointment.doctor?.id ?? "",
                        name: appointment.doctor?.name ?? "",
                        nipp: appointment.doctor?.nipp ?? "",
                    },
                    date: appointment.date ?? "",
                };
            },
            findAppointment() {
                this.selectedAppointmentID = document.querySelector('#appointment_id').value;
                const appointment = this.appointments.find((a) => a.id === this.selectedAppointmentID);
                this.isDataShown = Boolean(appointment);

                if (appointment) {
                    this.setDataFromAppointment(appointment);
                    this.appointmentServiceList = JSON.parse(appointment.services).map((s) => {
                        const selectedService = this.services.find((ss) => s.id === ss.id);
                        return {
                            id: s.id ?? "",
                            lower_price: selectedService?.lower_price ?? 0,
                            upper_price: selectedService?.upper_price ?? 0,
                            helper_text: `{{ __('form.helpers.pricing', ['lower' => 'lower', 'higher' => 'higher']) }}`
                                .replace('lower', convertRupiah(Number(selectedService?.lower_price)))
                                .replace('higher', convertRupiah(Number(selectedService?.upper_price))) ??
                                `{{ __('form.helpers.service') }}`,
                        };
                    });
                    this.installmentList = appointment.remaining_installment_steps.map((t) => ({
                        id: t.id,
                        type: t.type,
                        step: t.step,
                        amount: t.amount,
                        due_date: t.due_date,
                        status: t.status,
                    }));
                }
            },
            handleAppointmentChange(event) {
                this.selectedAppointmentID = event.target.value;
                this.findAppointment();
            },
            handleAddService() {
                this.appointmentServiceList.push({
                    id: "",
                    lower_price: 0,
                    upper_price: 0,
                    helper_text: `{{ __('form.helpers.service') }}`,
                });
            },
            handleInstallmentChange() {
                if (this.installmentSimulation.down_payment === 0 || this.installmentSimulation.installment_period ===
                    0) {
                    this.installmentSimulation.installment_amount = 0;
                } else {
                    this.installmentSimulation.installment_amount = (this.billTotal - this.installmentSimulation
                            .down_payment) /
                        this.installmentSimulation.installment_period;
                }
            },
            handleRemoveService(index) {
                this.billTotal -= ((this.priceList[index] * this.quantityList[index]) - this.discountList[index]);
                this.discount -= this.discountList[index];
                this.currentPrice -= this.priceList[index] * this.quantityList[index];
                this.priceList.splice(index, 1);
                this.discountList.splice(index, 1);
                this.appointmentServiceList.splice(index, 1);
            },
            handleServiceChange(index, event) {
                const service = this.services.find(s => s.id === event.target.value);
                const data = {
                    id: service?.id ?? "",
                    lower_price: service?.lower_price ?? 0,
                    upper_price: service?.upper_price ?? 0,
                    helper_text: `{{ __('form.helpers.pricing', ['lower' => 'lower', 'higher' => 'higher']) }}`
                        .replace('lower', convertRupiah(Number(service?.lower_price)))
                        .replace('higher', convertRupiah(Number(service?.upper_price))) ??
                        `{{ __('form.helpers.service') }}`,
                };

                const price = document.getElementById(`service-price-${index}`);
                const discount = document.getElementById(`service-discount-${index}`);
                const helper = document.getElementById(`service-helper-${index}`);

                if (price) {
                    price.setAttribute('min', data.lower_price);
                    price.setAttribute('max', data.upper_price);
                }
                if (discount) {
                    discount.setAttribute('max', data.upper_price);
                }
                if (helper) {
                    helper.innerText = data.helper_text;
                }

                this.appointmentServiceList[index] = data;
            },
            calculatePricing() {
                this.currentPrice = 0;
                this.discount = 0;
                this.billTotal = 0;

                this.appointmentServiceList.forEach((_, index) => {
                    const subtotal = Number(this.priceList[index] ?? 0) * Number(this.quantityList[index] ?? 0);
                    this.currentPrice += subtotal;
                    if (subtotal > 0) {
                        this.discount += Number(this.discountList[index] ?? 0);
                    }
                    this.billTotal = this.currentPrice - this.discount;
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

                if (discount) {
                    discount.setAttribute('max', this.appointmentServiceList[index].upper_price * event.target.value);
                }
                this.calculatePricing();
            },
            unmountAddService() {
                const latest = document.querySelector('#service-container *:last-child select');

                if (latest) {
                    initSelectable(latest);
                    $(latest).on('select2:select', (event) => {
                        const index = latest.getAttribute('id').split('-')[1];
                        appointmentState.handleServiceChange(index, event);
                    });
                }
            },
            unmountChangeAppointment() {
                const selects = document.querySelectorAll('select');

                selects.forEach((select) => {
                    initSelectable(select);

                    if (/^service-\d+/.test(select.getAttribute('id'))) {
                        $(select).on('select2:select', (event) => {
                            const index = select.getAttribute('id').split('-')[1];
                            appointmentState.handleServiceChange(index, event);
                        });
                    }
                });
            }
        };
    </script>
@endPushOnce
</x-app-layout>
