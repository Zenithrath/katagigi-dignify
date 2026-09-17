@extends('layouts.main-layout')

@section('_title', __('general.appointment.index._title'))
@section('header')
    <x-main-header title="{{ __('features.appointment') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="GENERAL.APPOINTMENT" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="main-table-container" x-data="appointmentLookup">
        <section class="heading">
            <div>
                <h1>{{ __('general.appointment.index._title') }}</h1>
                <p>{{ __('general.appointment.index._subtitle') }}</p>
            </div>

            @can('create appointment')
                <a href="{{ route('appointments.create') }}" class="clickable-primary py-2 px-4 rounded-md">
                    {{ __('general.appointment.index.action.add') }}
                </a>
            @endcan
        </section>

        @if (Session::has('success'))
            <div class="mb-8">
                <x-alerts.success message="{{ Session::get('success') }}" />
            </div>
        @endif

        @if (Session::has('error'))
            <div class="mb-8">
                <x-alerts.failed message="{{ Session::get('error') }}" />
            </div>
        @endif

        <section class="py-2 px-4 mt-4">
            <span x-text="`Found: ${pagination.total} entries.`"></span>
        </section>

        @role('admin|nurse')
            <section class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="column">{{ __('No.') }}</th>
                            <th scope="col" class="index-column">{{ __('general.appointment.index.table.patient') }}</th>
                            <th scope="col" class="column">{{ __('general.appointment.index.table.doctor') }}</th>
                            <th scope="col" class="column">{{ __('general.appointment.index.table.service') }}</th>
                            <th scope="col" class="column">{{ __('general.appointment.index.table.schedule') }}</th>
                            <th scope="col" class="column">{{ __('general.appointment.index.table.status') }}</th>
                            <th scope="col" class="action-column">
                                <span class="sr-only"></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <template x-if="appointmentList.length > 0 && !isLoading">
                            <template x-for="(appointment, index) in appointmentList" :key="appointment.id">
                                <tr>
                                    <td class="column" x-text="(pagination.page - 1) * pagination.limit + index + 1"></td>
                                    <td class="index-column w-64 truncate">
                                        <div class="flex flex-col items-start justify-center gap-0">
                                            <a
                                                :href="(
                                                    `{{ route('appointments.show', ['appointment' => 'appointment.id']) }}`
                                                )
                                                .replace('appointment.id', appointment.id)">
                                                <span x-text="appointment.patient_name"></span>
                                            </a>
                                            <small x-text="appointment.patient_code"></small>
                                        </div>
                                    </td>
                                    <td class="column w-64">
                                        <div class="flex flex-col">
                                            <span x-text="appointment.doctor_name"></span>
                                            <small>NIPP. <span x-text="appointment.doctor_nipp"></span></small>
                                        </div>
                                    </td>
                                    <td class="column">
                                        <div class="flex flex-col">
                                            <span x-text="count(appointment.services)+' Services'"></span>
                                        </div>
                                    </td>
                                    <td class="column w-32">
                                        <div class="flex flex-col">
                                            <span
                                                x-text="changeTimeFormat(appointment.time_start)+' - '+changeTimeFormat(appointment.time_end)"></span>

                                            <small x-text="appointment.date"></small>
                                        </div>
                                    </td>
                                    <td class="column w-32">
                                        <template
                                            x-if="appointment.canceled_at && !appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <small class="px-2 py-0.5 bg-red-100 border border-red-600 text-red-600 rounded-md">
                                                {{ __('general.appointment.index.table.canceled') }}
                                            </small>
                                        </template>

                                        <template
                                            x-if="appointment.confirmed_at && appointment.paid_at && appointment.recorded_at && !appointment.canceled_at">
                                            <small
                                                class="px-2 py-0.5 bg-blue-100 border border-blue-600 text-blue-600 rounded-md">
                                                {{ __('general.appointment.index.table.completed') }}
                                            </small>
                                        </template>

                                        <template
                                            x-if="!appointment.canceled_at && appointment.confirmed_at && appointment.paid_at && !appointment.recorded_at">
                                            <small
                                                class="px-2 py-0.5 bg-green-100 border border-green-600 text-green-600 rounded-md">
                                                {{ __('general.appointment.index.table.served and paid') }}
                                            </small>
                                        </template>

                                        <template
                                            x-if="!appointment.canceled_at && appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <small
                                                class="px-2 py-0.5 bg-yellow-100 border border-yellow-600 text-yellow-600 rounded-md">
                                                {{ __('general.appointment.index.table.confirmed') }}
                                            </small>
                                        </template>

                                        <template
                                            x-if="!appointment.canceled_at && !appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <small
                                                class="px-2 py-0.5 bg-gray-100 border border-gray-600 text-gray-600 rounded-md">
                                                {{ __('general.appointment.index.table.pending') }}
                                            </small>
                                        </template>
                                    </td>
                                    <td class="action-column">
                                        <div class="flex flex-col lg:flex-row justify-left gap-2">
                                            @can('update appointment')
                                                <template x-if="!appointment.confirmed_at">
                                                    <a :href="(
                                                        `{{ route('appointments.confirm', ['appointment' => 'appointment.id']) }}`
                                                    )
                                                    .replace('appointment.id', appointment.id)"
                                                        class="h-full">
                                                        {{ __('general.appointment.index.action.confirm') }}
                                                        <span class="sr-only" x-text="appointment.patient_name"></span>
                                                    </a>
                                                </template>
                                            @endcan
                                            @can('update appointment')
                                                <template x-if="!appointment.confirmed_at">
                                                    <a :href="(
                                                        `{{ route('appointments.edit', ['appointment' => 'appointment.id']) }}`
                                                    )
                                                    .replace('appointment.id', appointment.id)"
                                                        class="h-full">
                                                        {{ __('general.appointment.index.action.edit') }}
                                                        <span class="sr-only" x-text="appointment.patient_name"></span>
                                                    </a>
                                                </template>
                                            @endcan
                                            @can('delete appointment')
                                                <template x-if="!appointment.paid_at">
                                                    <form
                                                        :action="(
                                                            `{{ route('appointments.destroy', ['appointment' => 'appointment.id']) }}`
                                                        )
                                                        .replace('appointment.id', appointment.id)"
                                                        method="post">
                                                        @csrf
                                                        @method('delete')
                                                        <button
                                                            class="text-danger-600 hover:text-danger-500 active:text-danger-700 text-left"
                                                            type="submit">
                                                            {{ __('general.appointment.index.action.delete') }}<span
                                                                class="sr-only" x-text="appointment.patient_name"></span></button>
                                                    </form>
                                                </template>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        <template x-if="appointmentList.length == 0 && !isLoading">
                            <tr>
                                <td class="column text-center" colspan="7">
                                    <div class="h-24 w-full flex items-center justify-center">
                                        {{ __('general.appointment.index.table.empty') }}
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </section>
        @endrole

        @role('doctor')
            <section class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="index-column">{{ __('general.appointment.index.table.patient') }}</th>
                            <th scope="col" class="column">{{ __('general.appointment.index.table.doctor') }}</th>
                            <th scope="col" class="column">{{ __('general.appointment.index.table.service') }}</th>
                            <th scope="col" class="column">{{ __('general.appointment.index.table.schedule') }}</th>
                            <th scope="col" class="column">{{ __('general.appointment.index.table.status') }}</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <template x-if="appointmentList.length > 0 && !isLoading">
                            <template x-for="appointment in appointmentList" :key="appointment.id">
                                <tr>
                                    <td class="index-column w-64 truncate">
                                        <div class="flex flex-col items-start justify-center gap-0">
                                            <a
                                                :href="(
                                                    `{{ route('appointments.show', ['appointment' => 'appointment.id']) }}`
                                                )
                                                .replace('appointment.id', appointment.id)">
                                                <span x-text="appointment.patient_name"></span>
                                            </a>
                                            <small x-text="appointment.patient_code"></small>
                                        </div>
                                    </td>
                                    <td class="column w-64">
                                        <div class="flex flex-col">
                                            <span x-text="appointment.doctor_name"></span>
                                            <small>NIPP. <span x-text="appointment.doctor_nipp"></span></small>
                                        </div>
                                    </td>
                                    <td class="column">
                                        <div class="flex flex-col">
                                            <span x-text="count(appointment.services)+' Services'"></span>
                                        </div>
                                    </td>
                                    <td class="column w-32">
                                        <div class="flex flex-col">
                                            <span
                                                x-text="changeTimeFormat(appointment.time_start)+' - '+changeTimeFormat(appointment.time_end)"></span>

                                            <small x-text="appointment.date"></small>
                                        </div>
                                    </td>
                                    <td class="column w-32">
                                        <template
                                            x-if="appointment.canceled_at && !appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <small
                                                class="px-2 py-0.5 bg-red-100 border border-red-600 text-red-600 rounded-md">
                                                {{ __('general.appointment.index.table.canceled') }}
                                            </small>
                                        </template>

                                        <template
                                            x-if="appointment.confirmed_at && appointment.paid_at && appointment.recorded_at && !appointment.canceled_at">
                                            <small
                                                class="px-2 py-0.5 bg-blue-100 border border-blue-600 text-blue-600 rounded-md">
                                                {{ __('general.appointment.index.table.completed') }}
                                            </small>
                                        </template>

                                        <template
                                            x-if="!appointment.canceled_at && appointment.confirmed_at && appointment.paid_at && !appointment.recorded_at">
                                            <small
                                                class="px-2 py-0.5 bg-green-100 border border-green-600 text-green-600 rounded-md">
                                                {{ __('general.appointment.index.table.served and paid') }}
                                            </small>
                                        </template>

                                        <template
                                            x-if="!appointment.canceled_at && appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <small
                                                class="px-2 py-0.5 bg-yellow-100 border border-yellow-600 text-yellow-600 rounded-md">
                                                {{ __('general.appointment.index.table.confirmed') }}
                                            </small>
                                        </template>

                                        <template
                                            x-if="!appointment.canceled_at && !appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <small
                                                class="px-2 py-0.5 bg-gray-100 border border-gray-600 text-gray-600 rounded-md">
                                                {{ __('general.appointment.index.table.pending') }}
                                            </small>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </section>
        @endrole

        <section class="mt-4">
            <nav class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 sm:px-0">
                <div class="-mt-px flex w-0 flex-1">
                    <template x-if="pagination.page > 1">
                        <button type="button" @click="handlePreviousPage()"
                            class="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                            <svg class="mr-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M18 10a.75.75 0 01-.75.75H4.66l2.1 1.95a.75.75 0 11-1.02 1.1l-3.5-3.25a.75.75 0 010-1.1l3.5-3.25a.75.75 0 111.02 1.1l-2.1 1.95h12.59A.75.75 0 0118 10z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="hidden md:block">{{ __('Previous') }}</span>
                        </button>
                    </template>
                </div>

                <div class="md:-mt-px flex">
                    <template x-if="pagination.page - 3 >= 0">
                        <button type="button" x-text="1"
                            :class="`mx-1 inline-flex items-center px-4 py-2 text-sm font-medium rounded-b-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800`"
                            @click="pagination.page = 1; lookup()"></button>
                    </template>

                    <template x-if="pagination.page - 3 > 0">
                        <div
                            :class="`mx-1 inline-flex items-center px-4 py-2 text-sm font-medium rounded-b-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800`">
                            ...</div>
                    </template>

                    <template x-for="page in pagination.last" :key="page">
                        <template x-if="page > (pagination.page - 2) && page < (pagination.page + 2)">
                            <button type="button" x-text="page"
                                :class="`mx-1 inline-flex items-center px-4 py-2 text-sm font-medium rounded-b-md hover:bg-gray-50 dark:hover:bg-gray-800 ${(pagination.page == page) ? 'text-indigo-600 border-t-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700'}`"
                                @click="pagination.page = page; lookup()"></button>
                        </template>
                    </template>

                    <template x-if="pagination.page + 2 < pagination.last">
                        <div
                            :class="`mx-1 inline-flex items-center px-4 py-2 text-sm font-medium rounded-b-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800`">
                            ...</div>
                    </template>

                    <template x-if="pagination.page + 2 <= pagination.last">
                        <button type="button" x-text="pagination.last"
                            :class="`mx-1 inline-flex items-center px-4 py-2 text-sm font-medium rounded-b-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800`"
                            @click="pagination.page = pagination.last; lookup()"></button>
                    </template>
                </div>

                <div class="-mt-px flex w-0 flex-1 justify-end">
                    <template x-if="pagination.page < pagination.last">
                        <button type="button" @click="handleNextPage()"
                            class="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                            <span class="hidden md:block">{{ __('Next') }}</span>
                            <svg class="ml-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M2 10a.75.75 0 01.75-.75h12.59l-2.1-1.95a.75.75 0 111.02-1.1l3.5 3.25a.75.75 0 010 1.1l-3.5 3.25a.75.75 0 11-1.02-1.1l2.1-1.95H2.75A.75.75 0 012 10z"
                                    clip-rule="evenodd" />s
                            </svg>
                        </button>
                    </template>
                </div>
            </nav>
        </section>
    </main>
@endsection

@pushOnce('scripts')
    <script type="text/javascript">
        function count(string) {
            const jsonObject = JSON.parse(string);
            const arrayLength = jsonObject.length;

            return arrayLength;
        }

        function changeTimeFormat(time) {
            var parts = time.split(':');
            var hours = parts[0];
            var minutes = parts[1];

            var formattedTime = hours + ':' + minutes;
            return formattedTime;
        }

        const appointmentLookup = {
            keyword: "",
            appointmentList: [],
            isLoading: false,
            pagination: {
                page: 1,
                limit: 20,
                last: 1,
                total: 1,
            },
            init() {
                this.lookup();
            },
            lookup() {
                this.isLoading = true;
                this.appointmentList = [];
                let params = new URLSearchParams({
                    keyword: this.keyword,
                    page: this.pagination.page,
                    limit: this.pagination.limit,
                });
                @role('admin|nurse')
                    fetch(`{{ route('api.appointments.lookup') }}?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            this.appointmentList = data.data;
                            this.pagination = {
                                ...this.pagination,
                                last: data.pagination.last,
                                total: data.pagination.total,
                            };
                            this.isLoading = false;
                        });
                @endrole
                @role('doctor')
                    fetch(`{{ route('api.appointments.lookup.doctor') }}?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            this.appointmentList = data.data;
                            this.pagination = {
                                ...this.pagination,
                                last: data.pagination.last,
                                total: data.pagination.total,
                            };
                            this.isLoading = false;
                        });
                @endrole
            },
            handleNextPage() {
                this.pagination.page++;
                this.lookup();
            },
            handlePreviousPage() {
                this.pagination.page--;
                this.lookup();
            },
        }
    </script>
@endpushOnce
