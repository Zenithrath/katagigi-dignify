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
    <main class="main-table-container" x-data="dataState">
        <section class="heading">
            <div>
                <h1>{{ __('report.transaction.index._title') }}</h1>
                <p>{{ __('report.transaction.index._subtitle') }}</p>
            </div>

            {{-- @can('create transaction') --}}
            @role('nurse|admin')
                <a href="{{ route('transactions.create') }}" class="clickable-primary py-2 px-4 rounded-md">
                    {{ __('report.transaction.index.actions.add') }}
                </a>
            @endrole
            {{-- @endcan --}}
        </section>

        <section class="content-card">
            <form>
                <div class="flex flex-col gap-2 items-start">
                    <div class="flex flex-col sm:flex-row gap-2 w-full">
                        <div class="input-group w-full flex-1">
                            <label for="date_start">{{ __('report.transaction.index.labels.date_start') }}</label>
                            <input type="date" name="date_start" id="date_start" class="flex-1 h-12"
                                placeholder="{{ __('report.transaction.index.placeholders.type_here') }}"
                                x-model="dateSince" />
                        </div>

                        <div class="input-group w-full flex-1">
                            <label for="date_end">{{ __('report.transaction.index.labels.date_end') }}</label>
                            <input type="date" name="date_end" id="date_end" class="flex-1 h-12"
                                placeholder="{{ __('report.transaction.index.placeholders.type_here') }}"
                                x-model="dateUntil" />
                        </div>
                    </div>

                    <div class="input-group w-full flex-1">
                        <label for="patient_keyword">{{ __('report.transaction.index.labels.patient_keyword') }}</label>
                        <input type="text" name="patient_keyword" id="patient_keyword" class="flex-1 h-12"
                            placeholder="{{ __('report.transaction.index.placeholders.type_here') }}" x-model="keyword" />
                        <small class="helper">
                            {{ __('report.transaction.index.helpers.patient_keyword') }}
                        </small>
                    </div>
                    <button class="clickable-primary py-2 px-4 rounded-md" @click.prevent="lookup()">
                        {{ __('report.transaction.index.actions.find') }}
                    </button>
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
                        <th scope="col" class="index-column">{{ __('report.transaction.index.table.patient') }}</th>
                        <th scope="col" class="column">{{ __('report.transaction.index.table.doctor') }}</th>
                        <th scope="col" class="column">{{ __('report.transaction.index.table.service') }}</th>
                        <th scope="col" class="column">{{ __('report.transaction.index.table.pricing') }}</th>
                        <th scope="col" class="column">{{ __('report.transaction.index.table.date') }}</th>
                        <th scope="col" class="column">{{ __('report.transaction.index.table.payment-method') }}</th>
                        <th scope="col" class="column"></th>
                        {{-- <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th> --}}
                    </tr>
                </thead>

                <tbody>
                    <template x-if="transactions.length > 0">
                        <template x-for="(transaction, index) in transactions" :key="transaction.id">
                            <tr>
                                <td class="column" x-text="(pagination.page - 1) * pagination.limit + index + 1"></td>
                                <td class="index-column w-72 overflow-hidden truncate">
                                    <div class="flex flex-col">
                                        <a :href="(`{{ route('transactions.show', ['transaction' => 'text-here']) }}`)
                                        .replace('text-here', transaction.id)"
                                            x-text="transaction.patient.name" class="mb-1 text-base"></a>
                                        <small class="helper"
                                            x-text="(`{{ __('report.transaction.index.table.patient_id', ['id' => 'text-here']) }}`)
                                            .replace('text-here', transaction.patient.code)"></small>
                                        <small class="helper"
                                            x-text="(`{{ __('report.transaction.index.table.patient_phone', ['phone' => 'text-here']) }}`)
                                            .replace('text-here', transaction.patient.phone)"></small>
                                    </div>
                                </td>
                                <td class="column w-72 overflow-hidden truncate">
                                    <div class="flex flex-col">
                                        <span x-text="transaction.doctor.name" class="mb-1 text-base"></span>
                                        <small class="helper"
                                            x-text="(`{{ __('report.transaction.index.table.doctor_nipp', ['nipp' => 'text-here']) }}`)
                                            .replace('text-here', transaction.doctor.nipp)"></small>
                                    </div>
                                </td>
                                <td class="column">
                                    <ul>
                                        <template x-for="item in transaction.services">
                                            <li x-text="`${item.code} - ${item.name}"></li>
                                        </template>
                                    </ul>
                                </td>
                                <td class="column" x-text="convertRupiah(transaction.price)">
                                </td>
                                <td class="column" x-text="new Date(transaction.created_at).toLocaleString()">
                                </td>
                                <td class="column" x-text="transaction.payment_method">
                                </td>
                                <td class="column">
                                    <template x-if="!transaction.canceled_at">
                                        <div
                                            class="w-fit px-2 py-0.5 bg-brand-500/30 border border-brand-500 text-brand-500 rounded-md text-xs">
                                            {{ __('Completed') }}
                                        </div>
                                    </template>
                                    <template x-if="transaction.canceled_at">
                                        <div
                                            class="w-fit px-2 py-0.5 bg-orange-500/30 border border-orange-500 text-orange-500 rounded-md text-xs">
                                            {{ __('Canceled') }}
                                        </div>
                                    </template>
                                </td>
                                {{-- <td class="action-column">
                                    <a :href="(`{{ route('transactions.show', ['transaction' => 'text-here']) }}`)
                                    .replace(`text-here`, transaction.id)"
                                        class="h-full">
                                        {{ __('report.transaction.index.actions.print') }}
                                        <span class="sr-only" x-text="transaction.patient.name"></span>
                                    </a>
                                </td> --}}
                            </tr>
                        </template>
                    </template>

                    <template x-if="transactions.length <= 0">
                        <tr>
                            <td class="column text-center" colspan="8">
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
                                {{ __('Previous') }}
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
                                {{ __('Next') }}
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
        const dataState = {
            keyword: "",
            dateSince: "",
            dateUntil: "",
            transactions: [],
            pagination: {
                page: 1,
                limit: 20,
                last: 1,
                total: 1,
            },
            convertRupiah(value) {
                // value = parseFloat(value.toFixed(2)).toString();
                const [integer, decimal] = value.split(',');
                const numberString = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                // return `Rp. ${numberString}.${decimal}`;
                return `Rp. ${numberString}`;
            },
            init() {
                this.lookup();
            },
            lookup() {
                this.transactions = [];

                const filters = {
                    keyword: this.keyword,
                    since: this.dateSince,
                    until: this.dateUntil,
                    limit: this.pagination.limit,
                    page: this.pagination.page,
                }

                const params = new URLSearchParams(filters).toString();

                fetch("{{ route('api.transactions.find_transactions') }}?" + params)
                    .then(res => res.json())
                    .then(data => {
                        this.transactions = data.data;
                        this.pagination = {
                            ...this.pagination,
                            last: data.pagination.last,
                            total: data.pagination.total,
                        };
                        console.log('this.pagination', this.pagination)
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

        };
    </script>
@endPushOnce
