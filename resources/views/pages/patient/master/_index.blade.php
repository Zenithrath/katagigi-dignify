@extends('layouts.main-layout')

@section('_title', __('patient.master.index.title'))
@section('header')
    <x-main-header title="{{ __('features.patient') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="PATIENT.MASTER" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="main-table-container" x-data="patientLookup">
        <section class="heading">
            <div>
                <h1>{{ __('patient.master.index.title') }}</h1>
                <p>{{ __('patient.master.index.subtitle') }}</p>
            </div>

            @can('create patient')
                <a href="{{ route('patients.create') }}" class="clickable-primary py-2 px-4 rounded-md">
                    {{ __('patient.master.index.buttons.add') }}
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
            <form action="" method="GET">
                <div class="input-group">
                    <label for="keyword">{{ __('form.labels.patient_keyword') }}</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" name="keyword" id="keyword" class="flex-1"
                            placeholder="{{ __('form.placeholders.keyword') }}" x-model="keyword" />
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
                        <th scope="col" class="index-column">{{ __('patient.master.index.table.name') }}</th>
                        <th scope="col" class="column">{{ __('patient.master.index.table.mr') }}</th>
                        <th scope="col" class="column">{{ __('patient.master.index.table.address') }}</th>
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="patientList.length > 0 && !isLoading">
                        <template x-for="(patient, index) in patientList" :key="patient.id">
                            <tr>
                                <td class="column" x-text="(pagination.page - 1) * pagination.limit + index + 1"></td>
                                <td class="index-column flex gap-4 items-center w-80 overflow-hidden truncate">
                                    <div class="flex gap-4 items-center">
                                        <template x-if="patient.picture">
                                            <img :src="(`{{ asset('storage/picture') }}`.replace('picture', patient.picture))"
                                                :alt="`${patient.name}'s Picture`"
                                                class="w-12 h-12 object-cover object-center rounded-full" />
                                        </template>
                                        <template x-if="!patient.picture">
                                            <div
                                                class="w-12 h-12 fill-none stroke-1 stroke-slate-900 dark:stroke-slate-100">
                                                <x-icons.user-circle />
                                            </div>
                                        </template>
                                        <div class="flex flex-col">
                                            <span class="font-semibold">
                                                <a :href="(`{{ route('patients.show', ['patient' => 'patient.id']) }}`).replace(
                                                    'patient.id', patient.id)"
                                                    class="h-full">
                                                    <span x-text="patient.name"></span>
                                                </a>
                                            </span>
                                            <span class="text-gray-500" x-text="patient.email"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="column">
                                    <span x-text="patient.code"></span>
                                </td>
                                <td class="column">
                                    <span x-text="patient.village+', '+patient.district+', '+patient.regency"></span>
                                </td>
                                <td class="action-column">
                                    <div class="flex gap-2">
                                        <a :href="`{{ route('patients.edit', ['patient' => 'patient.id']) }}`.replace(
                                            'patient.id', patient.id)"
                                            class="h-full">
                                            {{ __('patient.master.index.buttons.edit') }}
                                            <span class="sr-only" x-text="patient.name"></span>
                                        </a>
                                        @can('delete patient')
                                            <form
                                                :action="`{{ route('patients.destroy', ['patient' => 'patient.id']) }}`.replace(
                                                    'patient.id', patient.id)"
                                                method="post">
                                                @csrf
                                                @method('delete')
                                                <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                    type="submit">{{ __('patient.master.index.buttons.delete') }}<span
                                                        class="sr-only" x-text="patient.name"></span></button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>

                    <template x-if="isLoading">
                        <tr>
                            <td class="column text-center" colspan="6">
                                <div class="h-24 w-full flex items-center justify-center">
                                    loading...
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="patientList.length <= 0 && !isLoading">
                        <tr>
                            <td class="column text-center" colspan="8">
                                <div class="h-24 w-full flex items-center justify-center">
                                    {{ __('patient.master.index.table.empty') }}
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

                <div class="md:-mt-px block">
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
                                {{ __('Previous') }}
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
        const patientLookup = {
            keyword: "",
            patientList: [],
            isLoading: false,
            pagination: {
                page: 1,
                limit: 20,
                last: 1,
                total: 1,
            },
            init() {
                @if (auth()->user()->hasRole('admin'))
                    this.lookup();
                @endif
            },
            lookup() {
                this.isLoading = true;
                this.patientList = [];
                let params = new URLSearchParams({
                    keyword: this.keyword,
                    page: this.pagination.page,
                    limit: this.pagination.limit,
                });
                fetch(`{{ route('api.patients.lookup') }}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        this.patientList = data.data;
                        this.pagination = {
                            ...this.pagination,
                            last: data.pagination.last,
                            total: data.pagination.total,
                        };
                        this.isLoading = false;
                    });
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
@endPushOnce
