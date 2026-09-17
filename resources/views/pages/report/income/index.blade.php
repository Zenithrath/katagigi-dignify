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
            <div class="flex flex-col items-end gap-4">
                <form action="" method="get" class="w-full">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 flex-wrap gap-2 w-full">
                        <div class="input-group flex flex-col gap-2 flex-1">
                            <label for="since">{{ __('report.income.index.lookup.since') }}</label>
                            <input type="date" name="since" id="since" x-model="dateSince" />
                        </div>
                        <div class="input-group flex flex-col gap-2 flex-1">
                            <label for="until">{{ __('report.income.index.lookup.until') }}</label>
                            <input type="date" name="until" id="until" x-model="dateUntil" />
                        </div>
                        <div class="input-group flex flex-col gap-2 flex-1">
                            <label for="doctor">{{ __('report.income.index.lookup.doctor.title') }}</label>
                            <select name="doctor" id="doctor" x-model="doctorID">
                                @if (auth()->user()->hasRole('admin'))
                                    <option value="">{{ __('report.income.index.lookup.doctor.helper') }}</option>
                                @endif
                                @foreach ($doctors as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 w-full">
                    <small class="helper">
                        {{ __('report.income.index.lookup._helper') }}
                    </small>
                    <div class="flex gap-2">
                        <button class="clickable-ghost py-2 px-4 rounded-md md:w-fit text-sm" @click="handleExportClick()">
                            {{ __('Export XLSX') }}
                        </button>
                        <button class="clickable-primary py-2 px-4 rounded-md flex-1 md:w-fit"
                            @click="handleLookupClick()">{{ __('Filter') }}</button>
                    </div>
                </div>
            </div>
        </section>

        <template x-if="isEmpty || isLoading">
            <section class="content-card">
                <template x-if="isLoading && !isEmpty">
                    <div class="w-full h-32 flex items-center justify-center">
                        {{ __('Loading...') }}
                    </div>
                </template>

                <template x-if="isEmpty && !isLoading">
                    <div class="w-full h-32 flex items-center justify-center">
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
