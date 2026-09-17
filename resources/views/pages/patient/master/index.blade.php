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
    <main class="main-table-container">
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
            <form action="{{ route('patients.index') }}" method="GET">
                <div class="input-group">
                    <label for="keyword">{{ __('form.labels.patient_keyword') }}</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" name="keyword" id="keyword" class="flex-1" value="{{ request('keyword') }}"
                            placeholder="{{ __('form.placeholders.keyword') }}" />
                        <button class="clickable-primary py-2 px-4 rounded-md" type="submit">
                            {{ __('patient.record.index.actions.find') }}
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="py-2 px-4 mt-4">
            <span>Found: {{ $patientList->total() }} entries.</span>
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
                    @if (count($patientList) > 0)
                        @foreach ($patientList as $index => $patient)
                            <tr>
                                <td class="column">
                                    {{ ($patientList->currentPage()-1) * $patientList->perPage() + ++$index }}
                                </td>
                                <td class="index-column flex gap-4 items-center w-80 overflow-hidden truncate">
                                    <div class="flex gap-4 items-center">
                                        @if ($patient->picture)
                                            <img src="{{ asset('storage/' . $patient->picture) }}"
                                                alt="{{ $patient->name }}'s Picture"
                                                class="w-12 h-12 object-cover object-center rounded-full" />
                                        @else
                                            <div
                                                class="w-12 h-12 fill-none stroke-1 stroke-slate-900 dark:stroke-slate-100">
                                                <x-icons.user-circle />
                                            </div>
                                        @endif
                                        <div class="flex flex-col">
                                            <span class="font-semibold">
                                                <a href="{{ route('patients.show', ['patient' => $patient->id]) }}"
                                                    class="h-full">
                                                    <span>{{ $patient->name }}</span>
                                                </a>
                                            </span>
                                            <span class="text-gray-500">{{ $patient->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="column">
                                    <span>{{ $patient->code }}</span>
                                </td>
                                <td class="column">
                                    <span>{{ implode(', ', array_filter([$patient->village, $patient->district, $patient->regency], fn($value) => !is_null($value) && $value !== '')) }}</span>
                                </td>
                                <td class="action-column">
                                    <div class="flex gap-2">
                                        <a href="{{ route('patients.edit', ['patient' => $patient->id]) }}" class="h-full">
                                            {{ __('patient.master.index.buttons.edit') }}
                                            <span class="sr-only">{{ $patient->name }}</span>
                                        </a>
                                        @can('delete patient')
                                            <form action="{{ route('patients.destroy', ['patient' => $patient->id]) }}"
                                                method="post">
                                                @csrf
                                                @method('delete')
                                                <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                    type="submit">{{ __('patient.master.index.buttons.delete') }}<span
                                                        class="sr-only">{{ $patient->name }}</span></button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="column text-center" colspan="6">
                                <div class="h-24 w-full flex items-center justify-center">
                                    {{ __('patient.master.index.table.empty') }}
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </section>

        <section class="mt-4">
            <nav class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 sm:px-0">
                <!-- Previous Page Link -->
                <div class="-mt-px flex w-0 flex-1">
                    @if ($patientList->onFirstPage() === false)
                        <a href="{{ $patientList->previousPageUrl() }}"
                            class="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                            <svg class="mr-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M18 10a.75.75 0 01-.75.75H4.66l2.1 1.95a.75.75 0 11-1.02 1.1l-3.5-3.25a.75.75 0 010-1.1l3.5-3.25a.75.75 0 111.02 1.1l-2.1 1.95h12.59A.75.75 0 0118 10z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="hidden md:block">{{ __('Previous') }}</span>
                        </a>
                    @endif
                </div>

                <!-- Page Number Links -->
                <div class="md:-mt-px flex">
                    @if ($patientList->currentPage() > 3)
                        <a href="{{ $patientList->url(1) }}"
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                            aria-current="page">{{ 1 }}</a>
                    @endif

                    @if ($patientList->currentPage() > 4)
                        <span
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500">...</span>
                    @endif

                    @foreach (range(1, $patientList->lastPage()) as $page)
                        @if ($page >= $patientList->currentPage() - 2 && $page <= $patientList->currentPage() + 2)
                            <a href="{{ $patientList->url($page) }}"
                                class="{{ $page === $patientList->currentPage() ? 'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-indigo-600 border-indigo-500' : 'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700' }}"
                                aria-current="page">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($patientList->currentPage() + 2 < $patientList->lastPage() - 1)
                        <span
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500">...</span>
                    @endif

                    @if ($patientList->currentPage() + 2 < $patientList->lastPage())
                        <a href="{{ $patientList->url($patientList->lastPage()) }}"
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                            aria-current="page">{{ $patientList->lastPage() }}</a>
                    @endif
                </div>

                <!-- Next Page Link -->
                <div class="-mt-px flex w-0 flex-1 justify-end">
                    @if ($patientList->hasMorePages())
                        <a href="{{ $patientList->nextPageUrl() }}"
                            class="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                            <span class="hidden md:block">{{ __('Next') }}</span>
                            <svg class="ml-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M2 10a.75.75 0 01.75-.75h12.59l-2.1-1.95a.75.75 0 111.02-1.1l3.5 3.25a.75.75 0 010 1.1l-3.5 3.25a.75.75 0 11-1.02-1.1l2.1-1.95H2.75A.75.75 0 012 10z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif
                </div>
            </nav>
        </section>
    </main>
@endsection
