@extends('layouts.main-layout')

@section('_title', __('patient.record.index._title'))
@section('header')
    <x-main-header title="{{ __('features.medical-record') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="PATIENT.RECORD" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="main-table-container" x-data="recordLookup">
        <section class="heading">
            <div>
                <h1>{{ __('patient.record.index._title') }}</h1>
                <p>{{ __('patient.record.index._subtitle') }}</p>
            </div>

            @can('create medical record')
                <a href="{{ route('medical-records.create') }}" class="clickable-primary py-2 px-4 rounded-md">
                    {{ __('patient.record.index.actions.add') }}
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

        <section class="bg-slate-50 dark:bg-slate-900 dark:border dark:border-slate-700 p-8 rounded-md">
            <form>
                <div class="input-group">
                    <label for="keyword">{{ __('patient.record.index.table.patient_keyword') }}</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" name="keyword" id="keyword" class="flex-1"
                            placeholder="{{ __('patient.record.index.placeholders.patient_id') }}" x-model="keyword" />
                        <button class="clickable-primary py-2 px-4 rounded-md" @click.prevent="lookup()">
                            {{ __('patient.record.index.actions.find') }}
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="py-2 px-4 mt-4">
            <span x-text="`Found: ${pagination.total} entries.`"></span>
        </section>

        <section class="table-content">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="column">{{ __('No.') }}</th>
                        <th scope="col" class="index-column w-72">{{ __('patient.record.index.table.patient') }}</th>
                        <th scope="col" class="column">{{ __('patient.record.index.table.phone') }}</th>
                        <th scope="col" class="column">{{ __('patient.record.index.table.service') }}</th>
                        <th scope="col" class="column w-56">{{ __('patient.record.index.table.doctor') }}</th>
                        <th scope="col" class="column">{{ __('patient.record.index.table.date') }}</th>
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="recordList.length > 0 && !isLoading">
                        <template x-for="(record, index) in recordList" :key="record.id">
                            <tr>
                                <td class="column" x-text="(pagination.page - 1) * pagination.limit + index + 1"></td>
                                <td class="column w-72">
                                    <div class="flex flex-col">
                                        <a :href="`{{ route('medical-records.show', ['medical_record' => 'mrid']) }}`.replace(
                                            'mrid', record.id)"
                                            class="text-base mb-1" x-text="record.patient_name"></a>
                                        <span x-text="record.patient_code"></span>
                                        <span class="w-72 truncate" x-text="record.patient_address"></span>
                                    </div>
                                </td>
                                <td class="column" x-text="record.patient_phone"></td>
                                <td class="column">
                                    <ul>
                                        <template x-for="item in JSON.parse(record.services)">
                                            <li x-text="`${item.code} - ${item.name}`"></li>
                                        </template>
                                    </ul>
                                </td>
                                <td class="column w-56" x-text="record.doctor_name"></td>
                                <td class="column" x-text="record.appointment_date"></td>
                                <td class="action-column">
                                    <div class="flex gap-2">
                                        @role('admin|doctor')
                                            <a :href="(`{{ route('medical-records.edit', ['medical_record' => 'record.id']) }}`)
                                            .replace(`record.id`, record.id)"
                                                class="h-full">
                                                {{ __('patient.record.index.actions.edit') }}
                                                <span class="sr-only" x-text="record.patient_name"></span>
                                            </a>
                                        @endrole
                                        @role('admin|doctor')
                                            <form
                                                :action="(
                                                    `{{ route('medical-records.destroy', ['medical_record' => 'record.id']) }}`
                                                )
                                                .replace(`record.id`, record.id)"
                                                method="post">
                                                @csrf
                                                @method('delete')
                                                <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                    type="submit">{{ __('patient.record.index.actions.delete') }}<span
                                                        class="sr-only" x-text="record.patient_name"></span></button>
                                            </form>
                                        @endrole
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>

                    <template x-if="isLoading">
                        <tr>
                            <td class="column text-center" colspan="7">
                                <div class="h-24 w-full flex items-center justify-center">
                                    loading...
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="recordList.length <= 0 && !isLoading">
                        <tr>
                            <td class="column text-center" colspan="7">
                                <div class="h-24 w-full flex items-center justify-center">
                                    {{ __('patient.record.index.table.empty') }}
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </section>

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
                            <span class="hidden md:block">
                                {{ __('patient.record.index.buttons.previous') }}
                            </span>
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
                            <span class="hidden md:block">
                                {{ __('patient.record.index.buttons.next') }}
                            </span>
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
        const recordLookup = {
            keyword: "",
            recordList: [],
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
                this.recordList = [];
                let params = new URLSearchParams({
                    keyword: this.keyword,
                    limit: this.pagination.limit,
                    page: this.pagination.page
                });
                fetch(`{{ route('api.medical-records.lookup') }}?${params}`)
                    .then((res) => res.json())
                    .then((data) => {
                        this.recordList = data.data;
                        this.pagination = {
                            ...this.pagination,
                            last: data.pagination.last,
                            total: data.pagination.total,
                        };
                        this.isLoading = false;
                    });
            },
            handleNextPage() {
                this.pagination.page += 1;
                this.lookup();
            },
            handlePreviousPage() {
                this.pagination.page -= 1;
                this.lookup();
            }
        }
    </script>
@endPushOnce
