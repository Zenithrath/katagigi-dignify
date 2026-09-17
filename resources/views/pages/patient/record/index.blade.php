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
    <main class="main-table-container">
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
            <form action="{{ route('medical-records.index') }}" method="GET">
                <div class="input-group">
                    <label for="keyword">{{ __('patient.record.index.table.patient_keyword') }}</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" name="keyword" id="keyword" class="flex-1" value="{{ request('keyword') }}"
                            placeholder="{{ __('patient.record.index.placeholders.patient_id') }}" />
                        <button class="clickable-primary py-2 px-4 rounded-md" type="submit">
                            {{ __('patient.record.index.actions.find') }}
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="py-2 px-4 mt-4">
            <span>Found: {{ $medicalRecordList->total() }} entries.</span>
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
                    @if (count($medicalRecordList) > 0)
                        @foreach ($medicalRecordList as $index => $record)
                            <tr>
                                <td class="column">
                                    {{ ($medicalRecordList->currentPage() - 1) * $medicalRecordList->perPage() + ++$index }}
                                </td>
                                <td class="column w-72">
                                    <div class="flex flex-col">
                                        <a href="{{ route('medical-records.show', ['medical_record' => $record->id]) }}"
                                            class="text-base mb-1">{{ $record->patient_name }}</a>
                                        <span>{{ $record->patient_code }}</span>
                                        <span class="w-72 truncate">{{ $record->patient_address }}</span>
                                    </div>
                                </td>
                                <td class="column">{{ $record->patient_phone }}</td>
                                <td class="column">
                                    <ul>
                                        @foreach (json_decode($record->services) as $item)
                                            <li>{{ $item->code }} - {{ $item->name }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="column w-56">{{ $record->doctor_name }}</td>
                                <td class="column">{{ $record->appointment_date }}</td>
                                <td class="action-column">
                                    <div class="flex gap-2">
                                        @role('admin|doctor')
                                            <a href="{{ route('medical-records.edit', ['medical_record' => $record->id]) }}"
                                                class="h-full">
                                                {{ __('patient.record.index.actions.edit') }}
                                                <span class="sr-only">{{ $record->patient_name }}</span>
                                            </a>
                                        @endrole
                                        @role('admin|doctor')
                                            <form
                                                action="{{ route('medical-records.destroy', ['medical_record' => $record->id]) }}"
                                                method="post">
                                                @csrf
                                                @method('delete')
                                                <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                    type="submit">{{ __('patient.record.index.actions.delete') }}<span
                                                        class="sr-only">{{ $record->patient_name }}</span></button>
                                            </form>
                                        @endrole
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="column text-center" colspan="6">
                                <div class="h-24 w-full flex items-center justify-center">
                                    {{ __('patient.record.index.table.empty') }}
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
                    @if ($medicalRecordList->onFirstPage() === false)
                        <a href="{{ $medicalRecordList->previousPageUrl() }}"
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
                    @if ($medicalRecordList->currentPage() > 3)
                        <a href="{{ $medicalRecordList->url(1) }}"
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                            aria-current="page">{{ 1 }}</a>
                    @endif

                    @if ($medicalRecordList->currentPage() > 4)
                        <span
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500">...</span>
                    @endif

                    @foreach (range(1, $medicalRecordList->lastPage()) as $page)
                        @if ($page >= $medicalRecordList->currentPage() - 2 && $page <= $medicalRecordList->currentPage() + 2)
                            <a href="{{ $medicalRecordList->url($page) }}"
                                class="{{ $page === $medicalRecordList->currentPage() ? 'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-indigo-600 border-indigo-500' : 'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700' }}"
                                aria-current="page">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($medicalRecordList->currentPage() + 2 < $medicalRecordList->lastPage() - 1)
                        <span
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500">...</span>
                    @endif

                    @if ($medicalRecordList->currentPage() + 2 < $medicalRecordList->lastPage())
                        <a href="{{ $medicalRecordList->url($medicalRecordList->lastPage()) }}"
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                            aria-current="page">{{ $medicalRecordList->lastPage() }}</a>
                    @endif
                </div>

                <!-- Next Page Link -->
                <div class="-mt-px flex w-0 flex-1 justify-end">
                    @if ($medicalRecordList->hasMorePages())
                        <a href="{{ $medicalRecordList->nextPageUrl() }}"
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
