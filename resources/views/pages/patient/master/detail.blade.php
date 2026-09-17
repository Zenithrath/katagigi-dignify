<x-app-layout>
    <x-slot:title>{{ __('patient.master.detail.title') }}</x-slot:title>

    <main class="mb-auto px-8 pt-8 pb-12">
        <div class="flex gap-4 items-center">
            <a href="{{ route('patients.index') }}" class="clickable-ghost w-8 h-8 rounded-md">
                <x-lucide-chevron-left class="w-full h-full" />
            </a>
            <h1> {{ __('patient.master.detail.title') }} </h1>
        </div>

        <div class="content-card relative mt-4 px-8">
            {{-- @if (isset($data->picture) && $data->picture)
                <img id="profile-preview" src="{{ isset($data->picture) ? asset('storage/' . $data->picture) : '' }}"
                    alt="" srcset="" class="absolute w-36 h-36 -top-16 rounded-full object-cover object-center" />
            @else
                <div
                    class="absolute w-36 h-36 -top-16 rounded-full bg-slate-300 border-4 border-slate-200 fill-none stroke-1 stroke-slate-200
                    <x-lucide-user-circle class="w-full h-full" />
                </div>
            @endif --}}


            <div x-data="{ tab: 1 }">
                <div class="flex gap-3 mb-8">
                    <button x-on:click="tab = 1"
                        class="py-2 px-4 rounded-md bg-brand-500 text-white">{{ __('patient.master.detail.labels.information') }}</button>
                    <button x-on:click="tab = 2"
                        class="py-2 px-4 rounded-md bg-brand-500 text-white">{{ __('patient.master.detail.labels.history') }}</button>
                </div>
                <div x-show="tab === 1">

                    {{-- <section id="name">
                        <div class="font-bold text-2xl font">{{ $data->name }}</div>
                    </section> --}}
                    <section id="information-title">
                        <span
                            class="font-bold text-2xl font">{{ __('patient.master.detail.labels.title.information') }}</span>
                    </section>

                    <section id="personal-data" class="mt-4">
                        <dl class="detail-list">
                            <div class="data-container">
                                <dt>{{ __('patient.master.detail.labels.name') }}</dt>
                                <div class="flex flex-col gap-1">
                                    <dd class="font-semibold">
                                        {{ $data->name ?? '-' }}
                                </div>
                            </div>
                            <div class="data-container">
                                <dt>{{ __('patient.master.detail.labels.email') }}</dt>
                                <div class="flex flex-col gap-1">
                                    @if ($data->payment_email == $data->email || $data->payment_email == '')
                                        <dd class="font-semibold">
                                            {{ $data->email ?? '-' }}
                                            ({{ __('patient.master.detail.labels.payment_email') }})</dd>
                                    @else
                                        <dd class="font-semibold">
                                            {{ $data->payment_email ?? '-' }}
                                            ({{ __('patient.master.detail.labels.payment_email') }})</dd>
                                    @endif

                                    @if (isset($data->payment_email) && $data->payment_email != '')
                                        <dd>
                                            {{ $data->email ?? '-' }}</dd>
                                    @endif
                                </div>
                            </div>
                            <div class="data-container">
                                <dt>{{ __('patient.master.detail.labels.sosmed') }}</dt>
                                <dd class="font-semibold flex flex-col gap-1">
                                    @if (isset($data->sosmed->facebook) && $data->sosmed->facebook)
                                        <div>
                                            {{ __('patient.master.detail.labels.sosmeds.facebook') }}:
                                            <a href="https://facebook.com/{{ '@' . $data->sosmed->facebook ?? '-' }}">
                                                {{ $data->sosmed->facebook ?? '-' }}
                                            </a>
                                        </div>
                                    @endif

                                    @if (isset($data->sosmed->instagram) && $data->sosmed->instagram != '')
                                        <div>
                                            {{ __('patient.master.detail.labels.sosmeds.instagram') }}:
                                            <a href="https://instagram.com/{{ '@' . $data->sosmed->instagram ?? '-' }}">
                                                {{ $data->sosmed->instagram ?? '-' }}
                                            </a>
                                        </div>
                                    @endif

                                    @if (isset($data->sosmed->tiktok) && $data->sosmed->tiktok != '')
                                        <div>
                                            {{ __('patient.master.detail.labels.sosmeds.tiktok') }}:
                                            <a href="https://tiktok.com/{{ '@' . $data->sosmed->tiktok ?? '-' }}">
                                                {{ $data->sosmed->tiktok ?? '-' }}
                                            </a>
                                        </div>
                                    @endif

                                    @if (isset($data->sosmed->twitter) && $data->sosmed->twitter != '')
                                        <div>
                                            {{ __('patient.master.detail.labels.sosmeds.twitter') }}:
                                            <a href="https://twitter.com/{{ '@' . $data->sosmed->twitter ?? '-' }}">
                                                {{ $data->sosmed->twitter ?? '-' }}
                                            </a>
                                        </div>
                                    @endif
                                </dd>

                            </div>
                            <div class="data-container">
                                <dt>{{ __('patient.master.detail.labels.phone') }}</dt>
                                <dd class="flex gap-2">
                                    <span>{{ $data->phone ?? '-' }}</span>
                                    <a href="{{ route('api.followup.whatsapp', ['phone' => $data->phone ?? '8', 'message' => 'Halo, ']) }}"
                                        class="clickable-primary px-2 py-1 text-xs rounded-md">Follow Up</a>
                                </dd>
                            </div>
                            <div class="data-container">
                                <dt>{{ __('patient.master.detail.labels.birthdate') }}</dt>
                                <dd>{{ $data->birthdate ?? '-' }}</dd>
                            </div>
                            <div class="data-container">
                                <dt>{{ __('patient.master.detail.labels.religion._title') }}</dt>
                                <dd>
                                    {{ $data->religion == 'ISLAM' ? __('patient.master.detail.labels.religion.islam') : '' }}
                                    {{ $data->religion == 'CHRISTIANITY' ? __('patient.master.detail.labels.religion.christianity') : '' }}
                                    {{ $data->religion == 'CATHOLIC' ? __('patient.master.detail.labels.religion.catholic') : '' }}
                                    {{ $data->religion == 'BUDDHISM' ? __('patient.master.detail.labels.religion.buddhism') : '' }}
                                    {{ $data->religion == 'HINDUISM' ? __('patient.master.detail.labels.religion.hinduism') : '' }}
                                    {{ $data->religion == 'OTHER' || $data->religion == '' ? __('patient.master.detail.labels.religion.other') : '' }}
                                </dd>
                            </div>
                            <div class="data-container">
                                <dt>{{ __('patient.master.detail.labels.gender._title') }}</dt>
                                <dd>
                                    {{ $data->gender == 'MALE' || $data->gender == '' ? __('patient.master.detail.labels.gender.male') : '' }}
                                    {{ $data->gender == 'FEMALE' ? __('patient.master.detail.labels.gender.female') : '' }}
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
                                ]) }}
                                </dd>
                            </div>
                        </dl>
                    </section>
                </div>
                <div x-show="tab === 2">
                    <section id="medical-title" class="mb-4">
                        <span class="font-bold text-2xl font">{{ __('patient.master.detail.labels.title.history') }}</span>
                    </section>

                    <section id="main-content-history">
                        {{-- <section class="bg-slate-50 p-8 rounded-md">
                            <form action="" method="GET">
                                <div class="input-group">
                                    <label for="keyword">{{ __('patient.record.index.table.patient_keyword_history') }}</label>
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <input type="text" name="keyword" id="keyword" class="flex-1"
                                            placeholder="{{ __('patient.record.index.placeholders.keyword') }}" x-model="keyword" />
                                        <button class="clickable-primary py-2 px-4 rounded-md" @click.prevent="lookup()">
                                            {{ __('patient.record.detail.actions.find') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </section> --}}

                        {{-- <section class="py-2 px-4 mt-4">
                            <span>Found: {{ count($data->records) }} entries.</span>
                        </section> --}}

                        <section id="history-data" class="mt-4">
                            @foreach ($data->records as $record)
                                <section class="content-card shadow-lg rounded-lg bg-gray-100">
                                    <div>
                                        <h1>{{ __('Medical Record') }} #{{ strtoupper(substr($record->id, 0, 7)) }}</h1>
                                        <p>{{ Carbon::parse($record->created_at)->locale('id')->setTimezone('Asia/Jakarta')->isoFormat('dddd, DD MMMM YYYY HH:mm ZZ') }}
                                        </p>
                                    </div>
                                    <dl class="detail-list">
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.patient.title') }}</dt>
                                            <dd class="flex flex-col gap-0">
                                                <span class="font-semibold">{{ $record->patient_name }}</span>
                                                <span>{{ __('patient.record.detail.data.patient.id') }}: {{ $record->patient_code }}</span>
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.doctor.title') }}</dt>
                                            <dd class="flex flex-col gap-0">
                                                <span class="font-semibold">{{ $record->doctor_name }}</span>
                                                <span>{{ __('patient.record.detail.data.doctor.nipp') }}: {{ $record->doctor_nipp }}</span>
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.service.title') }}</dt>
                                            <dd class="flex flex-col gap-2">
                                                @foreach ($record->services as $service)
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
                                                {{ Carbon::parse($record->next_schedule)->locale('id')->setTimezone('Asia/Jakarta')->isoFormat('DD MMMM YYYY') }}
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.total') }}</dt>
                                            <dd class="flex flex-col gap-0 font-bold">
                                                {{ $toRupiah((int) $record->price) }}
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('form.labels.checkup_result') }}</dt>
                                            <dd class="flex flex-col gap-0 font-bold">
                                                {{ $record->checkup_result ?? '-' }}
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.anamnesis') }}</dt>
                                            <dd class="flex flex-col gap-0 font-bold">
                                                {{ $record->anamnesis ?? '-' }}
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.diagnosis') }}</dt>
                                            <dd class="flex flex-col gap-0 font-bold">
                                                {{ $record->diagnosis }}
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.therapy') }}</dt>
                                            <dd class="flex flex-col gap-0 font-bold">
                                                {{ $record->therapy }}
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.prescription') }}</dt>
                                            <dd class="flex flex-col gap-0 font-bold">
                                                {{ $record->prescription ?? 'No Prescription' }}
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.promat') }}</dt>
                                            <dd class="flex flex-col gap-0 font-bold">
                                                {{ $record->promat }}
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.blood_pressure') }}</dt>
                                            <dd class="flex flex-col gap-0 font-bold">
                                                {{ $record->blood_pressure . ' mmHg' }}
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.cooperativity') }}</dt>
                                            <dd class="flex flex-col gap-0 font-bold">
                                                {{ $record->cooperativity }}
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.images.before') }}</dt>
                                            <dd class="grid flex-wrap gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                                @foreach ($record->image_before as $index => $image)
                                                    <img src="{{ asset('storage/' . $image) }}" alt="{{ $index }}" />
                                                @endforeach
                                            </dd>
                                        </div>
                                        <div class="preview-container py-2">
                                            <dt class="font-semibold">{{ __('patient.record.detail.data.images.after') }}</dt>
                                            <dd class="grid flex-wrap gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                                @foreach ($record->image_after as $index => $image)
                                                    <img src="{{ asset('storage/' . $image) }}" alt="{{ $index }}" />
                                                @endforeach
                                            </dd>
                                        </div>
                                    </dl>
                                </section>
                            @endforeach
                            {{-- <section class="table-content">
                                <table>
                                    <thead>
                                        <tr>
                                            <th scope="col" class="column">{{ __('No.') }}</th>
                                            <th scope="col" class="column">{{ __('patient.record.index.table.medical_record') }}</th>
                                            <th scope="col" class="column">{{ __('patient.record.index.table.service') }}</th>
                                            <th scope="col" class="column w-56">{{ __('patient.record.index.table.doctor') }}</th>
                                            <th scope="col" class="column">{{ __('patient.record.index.table.date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="recordList.length > 0 && !isLoading">
                                            <template x-for="(record, index) in recordList" :key="record.id">
                                                <tr>
                                                    <td class="column" x-text="(pagination.page - 1) * pagination.limit + index + 1"></td>
                                                    <td class="column">
                                                        <a :href="`{{ route('medical-records.show', ['medical_record' => 'mrid']) }}`.replace(
                                                            'mrid', record.id)"
                                                            class="text-base mb-1" target="_blank" x-text="'#' + record.id.slice(0, 7).toUpperCase()"></a>
                                                    </td>
                                                    <td class="column">
                                                        <ul>
                                                            <template x-for="item in JSON.parse(record.services)">
                                                                <li x-text="`${item.code} - ${item.name}`"></li>
                                                            </template>
                                                        </ul>
                                                    </td>
                                                    <td class="column w-56" x-text="record.doctor_name"></td>
                                                    <td class="column" x-text="record.appointment_date"></td>
                                                </tr>
                                            </template>
                                        </template>

                                        <template x-if="isLoading">
                                            <tr>
                                                <td class="column text-center" colspan="5">
                                                    <div class="h-24 w-full flex items-center justify-center">
                                                        loading...
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>

                                        <template x-if="recordList.length <= 0 && !isLoading">
                                            <tr>
                                                <td class="column text-center" colspan="5">
                                                    <div class="h-24 w-full flex items-center justify-center">
                                                        {{ __('patient.record.index.table.empty') }}
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </section> --}}
                        </section>
                    </section>

                </div>
            </div>
        </div>
    </main>


@pushOnce('scripts')
    <script type="text/javascript">
        function copyEmail() {
            let email = document.getElementById('email');
            let payment_email = document.getElementById('payment_email');
            payment_email.value = email.value;
        }
        // const recordLookup = {
        //     keyword: "",
        //     recordList: [],
        //     isLoading: false,
        //     pagination: {
        //         page: 1,
        //         limit: 1000,
        //         last: 1,
        //         total: 1,
        //     },
        //     init() {
        //         this.lookup();
        //     },
        //     lookup() {
        //         this.isLoading = true;
        //         this.recordList = [];
        //         let params = new URLSearchParams({
        //             keyword: this.keyword,
        //             limit: this.pagination.limit,
        //             page: this.pagination.page
        //         });
        //         fetch(`{{ route('api.medical-records.lookup.history', ['id' => $data->id]) }}?${params}`)
        //             .then((res) => res.json())
        //             .then((data) => {
        //                 console.log(data.data);
        //                 this.recordList = data.data;
        //                 this.pagination = {
        //                     ...this.pagination,
        //                     last: data.pagination.last,
        //                     total: data.pagination.total,
        //                 };
        //                 this.isLoading = false;
        //             });
        //     },
        //     handleNextPage() {
        //         this.pagination.page += 1;
        //         this.lookup();
        //     },
        //     handlePreviousPage() {
        //         this.pagination.page -= 1;
        //         this.lookup();
        //     }
        // }
    </script>
@endPushOnce
</x-app-layout>
