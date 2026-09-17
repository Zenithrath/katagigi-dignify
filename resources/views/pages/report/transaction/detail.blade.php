@extends('layouts.main-layout')

@section('_title', __('report.transaction.index._title'))
@section('header')
    <x-main-header title="{{ __('features.transaction') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="REPORT.TRANSACTION" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="main-table-container" x-data="reschedule">
        <div class="flex gap-4">
            <div class="flex">
                <a href="{{ route('transactions.index') }}" class="clickable-ghost w-8 h-8 rounded-md">
                    <x-icons.chevron-left />
                </a>
            </div>
            <section class="heading w-full">
                <div class="flex-1">
                    <h1>{{ __('report.transaction.index._title') }} {{ 'No. ' . $data->sequence }}</h1>
                    <p>{{ Carbon::parse($data->created_at)->locale('id')->setTimezone('Asia/Jakarta')->isoFormat('dddd, DD MMMM YYYY HH:mm ZZ') }}
                    </p>
                </div>
                <button class="clickable-primary py-2 px-4 rounded-md" @click="window.print()">
                    {{ __('report.transaction.index.actions.print') }}
                </button>
            </section>
        </div>

        <section class="content-card">
            @if ($data->canceled_at)
                <div class="bg-orange-400/30 border border-orange-500 rounded-md py-2 px-4 mb-4">
                    {{ __('report.transaction.detail.helper.canceled') }}
                    {{ Carbon::parse($data->canceled_at)->locale(Session::get('applocale') ?? 'en')->setTimezone('Asia/Jakarta')->isoFormat('dddd, DD MMMM YYYY HH:mm') }}
                    {{ __('report.transaction.detail.helper.reason') }} {{ $data->cancel_reason }}
                </div>
            @endif

            <dl class="detail-list">
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('report.transaction.detail.data.patient') }}</dt>
                    <dd class="flex flex-col gap-0">
                        <span class="font-semibold">{{ $data->patient->name }}</span>
                        <span>ID: {{ $data->patient->code }}</span>
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('report.transaction.detail.data.doctor') }}</dt>
                    <dd class="flex flex-col gap-0">
                        <span class="font-semibold">{{ $data->doctor->name }}</span>
                        <span>NIPP: {{ $data->doctor->nipp }}</span>
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('report.transaction.detail.data.service.title') }}</dt>
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
                                    <span
                                        class="text-semibold">{{ __('report.transaction.detail.data.service.discount') }}:
                                        {{ $toRupiah($service->discount) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('report.transaction.detail.data.schedule.recomendation') }}</dt>
                    <dd class="flex flex-col gap-0">
                        <template x-if="isInputShown">
                            <div class="input-group">
                                <input type="date"
                                    :value="date ? new Date(date).toISOString().substring(0, 10) :
                                        null"
                                    @change="handleChange(event)" />
                                <button type="button" @click="handleSubmit()"
                                    class="clickable-primary px-4 py-2 rounded-md">{{ __('Set') }}</button>
                            </div>
                        </template>
                        <template x-if="!isInputShown">
                            <div class="flex gap-4">
                                <span x-text="dateText"></span>
                                <button type="button" @click="handleRescheduleClick()"
                                    class="clickable-primary px-2 py-0.5 rounded-md">{{ __('report.transaction.detail.data.schedule.reschedule') }}</button>
                            </div>
                        </template>
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('report.transaction.detail.data.pricing.title') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $toRupiah($data->price) }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('report.transaction.detail.data.pricing.discount') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $toRupiah($data->discount) }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('report.transaction.detail.data.pricing.total') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $toRupiah($data->billing) }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('report.transaction.detail.data.pricing.current_payment') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        {{ $toRupiah($data->current_payment) }}
                    </dd>
                </div>
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('report.transaction.detail.data.pricing.installments') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('general.table.number_no') }}</th>
                                    <th>{{ __('general.installment.index.table.steps') }}</th>
                                    <th>{{ __('general.installment.index.table.amount') }}</th>
                                    <th>{{ __('general.installment.index.table.due_date') }}</th>
                                    <th>{{ __('general.installment.index.table.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data->installments as $step)
                                    <tr>
                                        <td class="column">{{ $loop->iteration }}</td>
                                        <td class="column text-left">{{ $getType($step->type, $step->step) }}</td>
                                        <td class="column text-right">{{ $toRupiah($step->amount) }}</td>
                                        <td class="column text-center">{{ $step->due_date }}</td>
                                        <td class="column text-center">{{ $step->status }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </dd>
                </div>
                {{-- voucher code --}}
                <div class="preview-container py-2">
                    <dt class="font-semibold">{{ __('report.transaction.detail.data.voucher.title') }}</dt>
                    <dd class="flex flex-col gap-0 font-bold">
                        <span>{{ strtoupper($data->voucher_code ?? '-') }}</span>
                    </dd>
                </div>
            </dl>

            @role('admin')
                @if (!$data->canceled_at)
                    <div class="w-full flex items-center justify-end text-xs mt-8">
                        <template x-if="!isAskingCancelation">
                            <div class="flex flex-col md:flex-row items-end md:items-center gap-4">
                                <span>
                                    {{ __('report.transaction.detail.helper.cancel') }}
                                </span>
                                <button type="button" @click="handleAskCancelation()"
                                    class="clickable-ghost hover:!bg-danger-500 hover:!border-danger-700 active:!bg-danger-600 active:!border-danger-700 py-2 px-4 rounded-md">
                                    {{ __('report.transaction.detail.button.cancel') }}
                                </button>
                            </div>
                        </template>

                        <template x-if="isAskingCancelation">
                            <form action="{{ route('transactions.cancel', ['id' => $data->id]) }}" method="post"
                                class="w-full">
                                @csrf
                                <div class="flex flex-col md:flex-row items-end md:items-center md:justify-end gap-4 w-full">
                                    <input type="text" name="cancel_reason" id="cancel_reason" class="w-full md:w-72"
                                        placeholder="Type for a cancelation reason..." />
                                    <button type="submit"
                                        class="clickable-ghost hover:!bg-danger-500 hover:!border-danger-700 active:!bg-danger-600 active:!border-danger-700 py-2 px-4 rounded-md">
                                        {{ __('report.transaction.detail.button.submit_cancel') }}
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
                @endif
            @endrole
        </section>
    </main>
@endsection

@section('printable')
    <section class="w-56 pt-12 px-2">
        <div class="w-full text-center flex flex-col items-center gap-4">
            <img src="/assets/logo.svg" alt="Logo Klinik" class="h-12" />
            <div class="flex gap-x-4 gap-y-1 flex-wrap justify-center">
                <div class="flex gap-0.5 items-center">
                    <div class="h-4 w-4 fill-none stroke stroke-slate-950">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="currentStroke"
                            stroke="currentStroke" fill="currentFill" class="w-full h-full">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" />
                            <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                            <path d="M16.5 7.5l0 .01" />
                        </svg>
                    </div>
                    <span class="text-[10px]">{{ __('katagigibjm') }}</span>
                </div>

                <div class="flex gap-0.5 items-center">
                    <div class="h-4 w-4 fill-none stroke stroke-slate-950">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="currentStroke"
                            stroke="currentStroke" fill="currentFill" class="w-full h-full">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                            <path
                                d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                        </svg>
                    </div>
                    <span class="text-[10px]">{{ __('+62 821 777557 95') }}</span>
                </div>

                <div class="flex gap-0.5 items-center">
                    <div class="h-4 w-4 fill-none stroke stroke-slate-950">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="currentStroke"
                            stroke="currentStroke" fill="currentFill" class="w-full h-full">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                            <path d="M3 7l9 6l9 -6" />
                        </svg>
                    </div>
                    <span class="text-[10px]">{{ __('katagigibjm@gmail.com') }}</span>
                </div>
            </div>
        </div>

        <table class="shadow-none text-xs mt-8">
            <tbody class="bg-transparent shadow-none">
                <tr class="bg-transparent even:bg-transparent">
                    <td>No.</td>
                    <td>:</td>
                    <td>{{ $data->sequence }}</td>
                </tr>
                <tr class="bg-transparent even:bg-transparent">
                    <td class="whitespace-nowrap">Voucher</td>
                    <td>:</td>
                    <td>{{ strtoupper($data->voucher_code) }}</td>
                </tr>
                <tr class="bg-transparent even:bg-transparent">
                    <td>Date</td>
                    <td>:</td>
                    <td>{{ Carbon::parse($data->created_at)->locale('id')->setTimezone('Asia/Jakarta')->isoFormat('DD-MM-YYYY HH:mm') }}
                    </td>
                </tr>
                <tr class="bg-transparent even:bg-transparent">
                    <td>Patient ID</td>
                    <td>:</td>
                    <td>{{ $data->patient->code }}</td>
                </tr>
                <tr class="bg-transparent even:bg-transparent">
                    <td>Name</td>
                    <td>:</td>
                    <td>{{ $data->patient->name }}</td>
                </tr>
                <tr class="bg-transparent even:bg-transparent">
                    <td>Phone</td>
                    <td>:</td>
                    <td>{{ $data->patient->phone }}</td>
                </tr>
            </tbody>
        </table>

        <table class="shadow-none text-xs mt-4">
            <thead class="bg-transparent border-b-0">
                <tr>
                    <th class="py-2 max-w-8">Qty</th>
                    <th class="py-2 pl-2">Price</th>
                    <th class="py-2 pl-2">Subtotal</th>
                </tr>
            </thead>
            <tbody class="bg-transparent shadow-none">
                @foreach ($data->services as $service)
                    <tr>
                        <td class="py-1" colspan="3">{{ $service->name }}</td>
                    </tr>
                    <tr class="bg-transparent even:bg-transparent">
                        <td class="py-1 max-w-8 text-left pl-4">{{ 'x' . $service->quantity }}</td>
                        <td class="py-1 pl-2 text-right whitespace-nowrap">{{ $toRupiah($service->price) }}</td>
                        <td class="py-1 pl-2 text-right whitespace-nowrap">{{ $toRupiah($service->subtotal) }}</td>
                    </tr>
                    @if ($service->discount > 0)
                        <tr>
                            <td class="py-1" colspan="2">{{ __('Diskon ' . $service->name) }}</td>
                            <td class="py-1 text-right">{{ __('(' . $toRupiah($service->discount) . ')') }}
                        </tr>
                    @endif
                @endforeach

                <tr class="border-b border-b-slate-950 bg-transparent even:bg-transparent font-bold">
                    <td class="py-2 bg-transparent text-left whitespace-nowrap">{{ __('Total Tagihan') }}</td>
                    <td class="py-2 bg-transparent text-right" colspan="2">{{ $toRupiah($data->billing) }}</td>
                </tr>

                <tr class="bg-transparent even:bg-transparent">
                    <td colspan="3" class="py-2 bg-transparent text-left font-bold">{{ __('Pembayaran') }}
                    </td>
                </tr>
                <tr class="border-b border-b-slate-950 bg-transparent even:bg-transparent">
                    <td class="py-1 bg-transparent text-left" colspan="2">{{ $data->payment_method }}</td>
                    <td class="py-1 bg-transparent text-right">{{ $toRupiah($data->current_payment) }}</td>
                </tr>

                @if ($data->installments->count() > 0)
                    <tr class="bg-transparent even:bg-transparent">
                        <td colspan="3" class="py-2 bg-transparent text-left font-bold">{{ __('Informasi Angsuran') }}
                        </td>
                    </tr>
                    @php
                        $instTotal = 0;
                    @endphp
                    @foreach ($data->installments as $item)
                        @php
                            $instTotal += $item->amount;
                        @endphp
                        <tr class="bg-transparent even:bg-transparent">
                            <td class="py-1 font-bold" colspan="2">{{ $getType($item->type, $item->step) }}</td>
                            <td class="py-1 w-2 text-right whitespace-nowrap">{{ __('general.phrases.' . $item->status) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 w-20 pl-4 text-left whitespace-nowrap" colspan="2">
                                {{ 'Max. ' . Carbon::parse($item->due_date)->format('d M Y') }}
                            </td>
                            <td class="py-1 w-20 text-right whitespace-nowrap">{{ $toRupiah($item->amount) }}</td>
                        </tr>
                    @endforeach

                    <tr class="border-b border-b-slate-950 bg-transparent even:bg-transparent font-bold">
                        <td class="py-2 bg-transparent text-left whitespace-nowrap">{{ __('Total Angsuran') }}</td>
                        <td class="py-2 bg-transparent text-right" colspan="2">{{ $toRupiah($data->billing) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="flex flex-col items-center justify-center mt-12">
            <span class="font-semibold text-lg">** Terima Kasih **</span>
            <span class="text-sm">Semoga sehat selalu!</span>
        </div>

        <div class="flex flex-col items-center justify-center mt-4">
            <span class="text-xs">Klinik Kata Gigi</span>
            <span class="text-xs text-center">Jl. Perdagangan No.2</span>
            <span class="text-xs text-center">Alalak Utara, Banjarmasin Utara</span>
            <span class="text-xs text-center">Kota Banjarmasin</span>
            <span class="text-xs">{{ __('Telp. +62 821 777557 95') }}</span>
        </div>
    </section>
@endsection

@pushOnce('scripts')
    <script type="text/javascript">
        const reschedule = {
            date: "",
            dateText: "-",
            isInputShown: false,
            isAskingCancelation: false,
            init() {
                this.date = "{{ $data->next_schedule }}"

                if (this.date == "") return;
                this.dateText = new Intl.DateTimeFormat('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }).format(new Date(this.date));
            },
            handleRescheduleClick() {
                this.isInputShown = true;
            },
            handleChange(event) {
                this.date = event.target.value;
            },
            handleSubmit() {
                fetch("{{ route('api.transactions.reschedule', ['id' => $data->id]) }}", {
                    method: "put",
                    headers: new Headers({
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Content-Type': 'application/json'
                    }),
                    body: JSON.stringify({
                        date: this.date
                    })
                }).then(() => {
                    this.dateText = new Intl.DateTimeFormat('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    }).format(new Date(this.date));
                    this.isInputShown = false;
                });
            },
            handleAskCancelation() {
                this.isAskingCancelation = true;
            },
        };
    </script>
@endPushOnce
