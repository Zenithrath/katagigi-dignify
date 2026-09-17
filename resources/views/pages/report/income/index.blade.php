<x-app-layout>
    <x-slot:title>{{ __('features.income') }}</x-slot:title>

    <main class="main-table-container" x-data="state">
        <section class="heading">
            <div>
                <h1>{{ __('report.income.index._title') }}</h1>
                <p>{{ __('report.income.index._subtitle') }}</p>
            </div>
        </section>

        <section class="content-card">
            <form action="" method="get" class="flex flex-col gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1.5 block">{{ __('report.income.index.lookup.since') }}</label>
                        <input type="date" name="since" id="since" x-model="dateSince" class="custom-input" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1.5 block">{{ __('report.income.index.lookup.until') }}</label>
                        <input type="date" name="until" id="until" x-model="dateUntil" class="custom-input" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1.5 block">{{ __('report.income.index.lookup.doctor.title') }}</label>
                        <select name="doctor" id="doctor" x-model="doctorID" class="custom-select">
                            @if (auth()->user()->hasRole('admin'))
                                <option value="">{{ __('report.income.index.lookup.doctor.helper') }}</option>
                            @endif
                            @foreach ($doctors as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <small class="text-xs text-slate-400">{{ __('report.income.index.lookup._helper') }}</small>
                    <div class="flex gap-2">
                        <button class="clickable-ghost py-2.5 px-5 rounded-xl text-sm font-bold" type="button" @click="handleExportClick()">{{ __('Export XLSX') }}</button>
                        <button class="clickable-primary py-2.5 px-5 rounded-xl text-sm font-bold" type="button" @click="handleLookupClick()">{{ __('Filter') }}</button>
                    </div>
                </div>
            </form>
        </section>

        <template x-if="isEmpty || isLoading">
            <section class="content-card">
                <template x-if="isLoading && !isEmpty">
                    <div class="w-full h-32 flex items-center justify-center text-sm text-slate-400">
                        {{ __('Loading...') }}
                    </div>
                </template>

                <template x-if="isEmpty && !isLoading">
                    <div class="w-full h-32 flex items-center justify-center text-sm text-slate-400">
                        {{ __('report.income.index.helper.empty') }}
                    </div>
                </template>
            </section>
        </template>

        <template x-if="!isEmpty && !isLoading">
            <section class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th class="index-column">{{ __('report.income.table.service') }}</th>
                            <th class="column">{{ __('report.income.table.price') }}</th>
                            <th class="column">{{ __('report.income.table.discount') }}</th>
                            <th class="column">{{ __('report.income.table.income') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="report in reportList">
                            <tr>
                                <td class="index-column">
                                    <div class="flex flex-col gap-2">
                                        <span class="font-semibold" x-text="report.service_name"></span>
                                        <span x-text="report.service_code + ' - ' + report.service_category"></span>
                                    </div>
                                </td>
                                <td class="column" x-text="convertRupiah(report.service_price)"></td>
                                <td class="column" x-text="convertRupiah(report.service_discount)"></td>
                                <td class="column" x-text="convertRupiah(report.service_income)"></td>
                            </tr>
                        </template>
                        <tr class="border-t border-slate-500">
                            <td class="index-column" class="font-semibold uppercase">
                                {{ __('Total') }}
                            </td>
                            <td class="column" x-text="convertRupiah(total.price)"></td>
                            <td class="column" x-text="convertRupiah(total.discount)"></td>
                            <td class="column" x-text="convertRupiah(total.billing)"></td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </template>
    </main>


@pushOnce('scripts')
    <script type="text/javascript">
        const state = {
            dateSince: "",
            dateUntil: "",
            doctorID: "",
            serviceID: "",
            total: {
                price: 0,
                discount: 0,
                billing: 0,
            },
            reportList: [],
            isLoading: false,
            isEmpty: true,
            init() {
                this.lookup();
            },
            lookup() {
                this.isLoading = true;
                this.reportList = [];

                const paramsString = new URLSearchParams({
                    doctor: this.doctorID,
                    since: this.dateSince,
                    until: this.dateUntil
                }).toString();

                this.total.price = 0;
                this.total.discount = 0;
                this.total.billing = 0;

                fetch("{{ route('api.incomes.lookup') }}?" + paramsString).then((res) => res.json())
                    .then((data) => {
                        if (!data.data.length) {
                            this.isEmpty = true;
                            this.isLoading = false;
                            return;
                        }

                        data.data.map((service) => {
                            this.reportList.push({
                                service_id: service.id,
                                service_name: service.name,
                                service_code: service.code,
                                service_category: service.category,
                                service_price: service.price ?? 0,
                                service_discount: service.discount ?? 0,
                                service_income: (service.price ?? 0) - (service.discount ?? 0),
                            });

                            this.total.price += service.price ?? 0;
                            this.total.discount += service.discount ?? 0;
                            this.total.billing += (service.price ?? 0) - (service.discount ?? 0);
                        });

                        this.isEmpty = false;
                    });
                this.isLoading = false;
            },
            handleLookupClick() {
                this.lookup();
            },
            handleExportClick() {
                const paramsString = new URLSearchParams({
                    doctor: this.doctorID,
                    since: this.dateSince,
                    until: this.dateUntil
                }).toString();

                window.location.href = `{{ route('export-transactions') }}?${paramsString}`;
            }
        }
    </script>
@endPushOnce
</x-app-layout>
