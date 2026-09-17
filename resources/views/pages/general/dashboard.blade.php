<x-app-layout>
    <x-slot:title>{{ __('features.dashboard') }}</x-slot:title>

    <main class="px-8 pt-8 pb-12 h-full flex flex-col">
        @role('admin')
            <h3 class="text-lg font-semibold mb-8 pl-2">{{ __('general.dashboard.statistic._title') }}</h3>

            <section class="statistic">
                <div class="bg-transparent py-12 sm:py-12">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <dl class="grid grid-cols-1 gap-x-8 gap-y-16 text-center lg:grid-cols-3">
                            <div class="mx-auto flex max-w-xs flex-col gap-y-4">
                                <dt class="text-base leading-7 text-gray-600
                                    {{ __('details.dashboard.transactions-this-month') }}
                                </dt>
                                <dd
                                    class="order-first text-3xl font-semibold tracking-tight text-gray-900 sm:text-3xl">
                                    {{ $data->transactions }}
                                </dd>
                            </div>
                            <div class="mx-auto flex max-w-xs flex-col gap-y-4">
                                <dt class="text-base leading-7 text-gray-600
                                    {{ __('details.dashboard.total-revenue') }}</dt>
                                <dd
                                    class="order-first text-3xl font-semibold tracking-tight text-gray-900 sm:text-3xl">
                                    {{ $data->revenue }}</dd>
                            </div>
                            <div class="mx-auto flex max-w-xs flex-col gap-y-4">
                                <dt class="text-base leading-7 text-gray-600
                                    {{ __('details.dashboard.patient-registered') }}
                                </dt>
                                <dd
                                    class="order-first text-3xl font-semibold tracking-tight text-gray-900 sm:text-3xl">
                                    {{ $data->patients }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            <section class="mb-8" x-data="chartData()">
                <h3 class="text-lg font-semibold mb-4 pl-2">{{ __('details.dashboard.chart.title') }}</h3>
                <section class="grid lg:grid-cols-2 gap-4 mb-4">
                    <div x-init="initChartLastYear()" class="p-4 bg-slate-50 rounded-md">
                        <h4 class="text-md font-semibold mb-4 p-4">{{ __('details.dashboard.chart.subtitle.last-year') }}
                        </h4>
                        <div id="chart-last-year" class="w-full"></div>
                    </div>
                    <div x-init="initChartThisYear()" class="p-4 bg-slate-50 rounded-md">
                        <h4 class="text-md font-semibold mb-4 p-4">{{ __('details.dashboard.chart.subtitle.this-year') }}
                        </h4>
                        <div id="chart-this-year" class="w-full"></div>
                    </div>
                </section>
                <section class="col-span-2 p-4 bg-slate-50 rounded-md" x-init="initChartLastMonth()">
                    <h4 class="text-md font-semibold mb-4 p-4">{{ __('details.dashboard.chart.subtitle.last-month') }}</h4>
                    <div id="chart-last-month" class="w-full"></div>
                </section>
            </section>

            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div></div>
                <div class="h-fit px-8 py-8 bg-slate-50 rounded-md">
                    <table class="table-auto shadow-none">
                        <thead class="bg-transparent text-xs">
                            <tr>
                                <th>{{ __('Doctor') }}</th>
                                <th>{{ __('Transactions') }}</th>
                                <th>{{ __('Medical Records') }}</th>
                            </tr>
                        </thead>

                        <tbody class="bg-transparent">
                            @for ($index = 0; $index < count($data->dataChart->labels); $index++)
                                <tr>
                                    <td class="py-1 px-2">{{ $data->dataChart->labels[$index] }}</td>
                                    <td class="text-center py-1 px-2">{{ $data->dataChart->datasets[0]->data[$index] }}</td>
                                    <td class="text-center py-1 px-2">{{ $data->dataChart->datasets[1]->data[$index] }}</td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                {{-- <div
                    class="h-fit col-span-1 md:col-span-2 flex items-center justify-center px-4 py-4 bg-slate-50 rounded-md">
                    <canvas id="myChart"></canvas>
                </div> --}}
            </section>
        @endrole

        @role('doctor|nurse')
            <h3 class="text-lg font-semibold mb-8 pl-2">{{ __('details.dashboard.compliance-statistic') }}</h3>

            {{-- <section class="statistic flex items-center justify-center">
                <div class="h-36 md:h-48 w-fit">
                    <canvas id="myChart"></canvas>
                </div>
            </section> --}}

            <section class="w-full px-4 py-4 md:hidden bg-slate-50 mt-8 rounded-md" x-data="schedule">
                <div class="w-full flex h-12 items-center justify-between sticky">
                    <div class="flex flex-col gap-1 items-center h-full">
                        <span class="text-xs font-thin">S</span>
                        <label for="mob-day-sun" class="relative cursor-pointer overflow-hidden h-full">
                            <input type="radio" name="day" id="mob-day-sun" value="0" x-model="currentDay"
                                class="peer absolute -left-full" @change="handleSetDay(0)" />
                            <span
                                class="bg-slate-300 peer-checked:bg-brand-300/70 rounded-full px-3 py-1 text-xs"
                                x-text="dateList[0]">
                            </span>
                        </label>
                    </div>

                    <div class="flex flex-col gap-1 items-center h-full">
                        <span class="text-xs font-thin">M</span>
                        <label for="mob-day-mon" class="relative cursor-pointer overflow-hidden h-full">
                            <input type="radio" name="day" id="mob-day-mon" value="1" x-model="currentDay"
                                class="peer absolute -left-full" @change="handleSetDay(1)" />
                            <span
                                class="bg-slate-300 peer-checked:bg-brand-300/70 rounded-full px-3 py-1 text-xs"
                                x-text="dateList[1]">
                            </span>
                        </label>
                    </div>

                    <div class="flex flex-col gap-1 items-center h-full">
                        <span class="text-xs font-thin">T</span>
                        <label for="mob-day-tue" class="relative cursor-pointer overflow-hidden h-full">
                            <input type="radio" name="day" id="mob-day-tue" value="2" x-model="currentDay"
                                class="peer absolute -left-full" @change="handleSetDay(2)" />
                            <span
                                class="bg-slate-300 peer-checked:bg-brand-300/70 rounded-full px-3 py-1 text-xs"
                                x-text="dateList[2]">
                            </span>
                        </label>
                    </div>

                    <div class="flex flex-col gap-1 items-center h-full">
                        <span class="text-xs font-thin">W</span>
                        <label for="mob-day-wed" class="relative cursor-pointer overflow-hidden h-full">
                            <input type="radio" name="day" id="mob-day-wed" value="3" x-model="currentDay"
                                class="peer absolute -left-full" @change="handleSetDay(3)" />
                            <span
                                class="bg-slate-300 peer-checked:bg-brand-300/70 rounded-full px-3 py-1 text-xs"
                                x-text="dateList[3]">
                            </span>
                        </label>
                    </div>

                    <div class="flex flex-col gap-1 items-center h-full">
                        <span class="text-xs font-thin">T</span>
                        <label for="mob-day-thu" class="relative cursor-pointer overflow-hidden h-full">
                            <input type="radio" name="day" id="mob-day-thu" value="4" x-model="currentDay"
                                class="peer absolute -left-full" @change="handleSetDay(4)" />
                            <span
                                class="bg-slate-300 peer-checked:bg-brand-300/70 rounded-full px-3 py-1 text-xs"
                                x-text="dateList[4]">
                            </span>
                        </label>
                    </div>

                    <div class="flex flex-col gap-1 items-center h-full">
                        <span class="text-xs font-thin">F</span>
                        <label for="mob-day-fri" class="relative cursor-pointer overflow-hidden h-full">
                            <input type="radio" name="day" id="mob-day-fri" value="5" x-model="currentDay"
                                class="peer absolute -left-full" @change="handleSetDay(5)" />
                            <span
                                class="bg-slate-300 peer-checked:bg-brand-300/70 rounded-full px-3 py-1 text-xs"
                                x-text="dateList[5]">
                            </span>
                        </label>
                    </div>

                    <div class="flex flex-col gap-1 items-center h-full">
                        <span class="text-xs font-thin">S</span>
                        <label for="mob-day-sat" class="relative cursor-pointer overflow-hidden h-full">
                            <input type="radio" name="day" id="mob-day-sat" value="6" x-model="currentDay"
                                class="peer absolute -left-full" @change="handleSetDay(6)" />
                            <span
                                class="bg-slate-300 peer-checked:bg-brand-300/70 rounded-full px-3 py-1 text-xs"
                                x-text="dateList[6]">
                            </span>
                        </label>
                    </div>
                </div>

                <div class="mt-4 max-h-96 overflow-scroll">
                    <div class="relative h-[1440px] pt-2 w-full">
                        @foreach (range(0, 23) as $item)
                            <div class="flex gap-1">
                                <span class="w-4 text-xs font-thin">{{ $item }}</span>
                                <div class="h-[60px] flex-1 border-t border-slate-300
                            </div>
                        @endforeach

                        <div class="absolute top-2 w-full h-[1440px] pl-6 pr-2 mt-0">
                            <div class="isolate relative h-full w-full">
                                <template x-for="appointment in selectedDayAppointmentList">
                                    <div class="absolute w-full rounded-md py-1" x-data="{ colorHex: pickColor() }"
                                        :style="`top: ${countMinutes(appointment.time_start)}px; height: 60px`">
                                        <div class="w-full h-full text-xs px-2 py-2 rounded-md"
                                            :style="`border-width: 1px; border-color: ${colorHex}; color: ${colorHex}`">
                                            <span x-text="appointment.time_start + ' - ' + appointment.time_end"></span>
                                            <span class="w-full truncate"
                                                x-text="appointment.patient_code + ' - ' + appointment.patient_name"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endrole
    </main>


@pushOnce('scripts')
    @role('doctor|nurse|admin')
        <script type="text/javascript">
            const currency_formatter = new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                maximumFractionDigits: 0,
            });
            const incomeChartData = JSON.parse(@json($data->incomeChart ?? []));

            function chartData() {
                return {
                    chart: null,
                    initChartLastYear() {
                        const options = {
                            series: [{
                                name: "Income",
                                data: incomeChartData.lastYear.datasets,
                                formatter: function(value) {
                                    return currency_formatter.format(value);
                                }
                            }],
                            chart: {
                                type: 'bar',
                                height: 350
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 4,
                                    borderRadiusApplication: 'end',
                                    horizontal: false,
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            xaxis: {
                                categories: incomeChartData.lastYear.labels,
                                labels: {
                                    rotate: -45,
                                },
                            },
                            yaxis: {
                                labels: {
                                    formatter: function(value) {
                                        return currency_formatter.format(value);
                                    }
                                }
                            },
                        };

                        this.chart = new ApexCharts(document.querySelector("#chart-last-year"), options);
                        this.chart.render();
                    },
                    initChartThisYear() {
                        const options = {
                            series: [{
                                name: "Income",
                                data: incomeChartData.thisYear.datasets,
                                formatter: function(value) {
                                    return currency_formatter.format(value);
                                }
                            }],
                            chart: {
                                type: 'bar',
                                height: 350
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 4,
                                    borderRadiusApplication: 'end',
                                    horizontal: false,
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            xaxis: {
                                categories: incomeChartData.thisYear.labels,
                                labels: {
                                    rotate: -45
                                },
                            },
                            yaxis: {
                                labels: {
                                    formatter: function(value) {
                                        return currency_formatter.format(value);
                                    }
                                }
                            },
                        };

                        this.chart = new ApexCharts(document.querySelector("#chart-this-year"), options);
                        this.chart.render();
                    },
                    initChartLastMonth() {
                        const options = {
                            series: [{
                                name: "Income",
                                data: incomeChartData.lastMonth.datasets,
                                formatter: function(value) {
                                    return currency_formatter.format(value);
                                }
                            }],
                            chart: {
                                type: 'bar',
                                height: 350
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 4,
                                    borderRadiusApplication: 'end',
                                    horizontal: false,
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            xaxis: {
                                categories: incomeChartData.lastMonth.labels,
                                labels: {
                                    rotate: -45
                                },
                            },
                            yaxis: {
                                labels: {
                                    formatter: function(value) {
                                        return currency_formatter.format(value);
                                    }
                                }
                            },
                        };

                        this.chart = new ApexCharts(document.querySelector("#chart-last-month"), options);
                        this.chart.render();
                    }
                };
            }
            const schedule = {
                appointmentList: [],
                dateList: [],
                selectedDayAppointmentList: [],
                currentDay: 0,
                init() {
                    const appointmentList = @json($data->appointments);

                    const today = new Date();
                    const sundayDate = today.getDate() - today.getDay();

                    for (let index = 0; index < 7; index++) {
                        this.dateList.push(sundayDate + index);
                        this.appointmentList.push([]);
                    }

                    appointmentList.forEach((element) => {
                        const date = new Date(element.date);
                        const weekdayIndex = date.getDay();
                        this.appointmentList[weekdayIndex].push(element);
                    });

                    this.currentDay = today.getDay();
                    this.handleSetDay(this.currentDay);
                },
                handleSetDay(index) {
                    this.currentDay = index;
                    this.selectedDayAppointmentList = this.appointmentList[index];
                },
                countMinutes(time) {
                    const timeElements = time.split(":");
                    return (+timeElements[0]) * 60 + (+timeElements[1]);
                },
                pickColor() {
                    const colors = ['#d946ef', '#a855f7', '#8b5cf6', '#6366f1', '#3b82f6', '#0ea5e9', '#06b6d4'];
                    const index = Math.floor(Math.random() * colors.length);
                    return colors[index];
                }
            };

            @role('doctor')
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('myChart').getContext('2d');

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Compliance'],
                            datasets: [{
                                    label: '# Transactions',
                                    data: [{{ $data->transactions }}],
                                    backgroundColor: [
                                        '#475569',
                                    ],
                                    borderColor: [
                                        '#1e293b',
                                    ],
                                    borderWidth: 1
                                },
                                {
                                    label: '# Medical Records',
                                    data: [{{ $data->medical_records }}],
                                    backgroundColor: [
                                        '#3b82f6'
                                    ],
                                    borderColor: [
                                        '#1d4ed8'
                                    ],
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            indexAxis: 'y',
                            scales: {
                                y: {
                                    beginAtZero: true
                                },
                                x: {
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            },
                            responsive: true,
                            maintainAspectratio: false
                        }
                    });
                });
            @endrole
        </script>
    @endrole
@endPushOnce
</x-app-layout>
