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
    <main class="main-table-container">
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
            <form action="{{ route('transactions.index') }}" method="GET">
                <div class="flex flex-col gap-2 items-start">
                    <div class="flex flex-col sm:flex-row gap-2 w-full">
                        <div class="input-group w-full flex-1">
                            <label for="date_start">{{ __('report.transaction.index.labels.date_start') }}</label>
                            <input type="date" name="since" id="date_start" class="flex-1 h-12"
                                value="{{ request('since') }}"
                                placeholder="{{ __('report.transaction.index.placeholders.type_here') }}" />
                        </div>

                        <div class="input-group w-full flex-1">
                            <label for="date_end">{{ __('report.transaction.index.labels.date_end') }}</label>
                            <input type="date" name="until" id="date_end" class="flex-1 h-12"
                                value="{{ request('until') }}"
                                placeholder="{{ __('report.transaction.index.placeholders.type_here') }}" />
                        </div>
                    </div>

                    <div class="input-group w-full flex-1">
                        <label for="patient_keyword">{{ __('report.transaction.index.labels.patient_keyword') }}</label>
                        <input type="text" name="keyword" id="patient_keyword" class="flex-1 h-12"
                            value="{{ request('keyword') }}"
                            placeholder="{{ __('report.transaction.index.placeholders.type_here') }}" />
                        <small class="helper">
                            {{ __('report.transaction.index.helpers.patient_keyword') }}
                        </small>
                    </div>
                    <button class="clickable-primary py-2 px-4 rounded-md" type="submit">
                        {{ __('report.transaction.index.actions.find') }}
                    </button>
                </div>
            </form>
        </section>

        <section class="py-2 px-4 mt-4">
            <span>Found: {{ $transactionList->total() }} entries.</span>
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
                    @if (count($transactionList) > 0)
                        @foreach ($transactionList as $index => $transaction)
                            <tr>
                                <td class="column">
                                    {{ ($transactionList->currentPage() - 1) * $transactionList->perPage() + ++$index }}
                                </td>
                                <td class="index-column w-72 overflow-hidden truncate">
                                    <div class="flex flex-col">
                                        <a href="{{ route('transactions.show', ['transaction' => $transaction->id]) }}"
                                            class="mb-1 text-base">{{ $transaction->patient->name }}</a>
                                        <small
                                            class="helper">{{ __('report.transaction.index.table.patient_id', ['id' => $transaction->patient->code]) }}</small>
                                        <small
                                            class="helper">{{ __('report.transaction.index.table.patient_phone', ['phone' => $transaction->patient->phone]) }}</small>
                                    </div>
                                </td>
                                <td class="column w-72 overflow-hidden truncate">
                                    <div class="flex flex-col">
                                        <span class="mb-1 text-base">{{ $transaction->doctor->name }}</span>
                                        <small
                                            class="helper">{{ __('report.transaction.index.table.doctor_nipp', ['nipp' => $transaction->doctor->nipp]) }}</small>
                                    </div>
                                </td>
                                <td class="column">
                                    <ul>
                                        @foreach ($transaction->services as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="column">{{ $toRupiah($transaction->price) }}</td>
                                <td class="column">
                                    {{ \Carbon\Carbon::parse($transaction->created_at)->format('Y-m-d H:i:s') }}</td>
                                <td class="column">{{ $transaction->payment_method }}</td>
                                <td class="column">
                                    @if (!$transaction->canceled_at)
                                        <div
                                            class="w-fit px-2 py-0.5 bg-brand-500/30 border border-brand-500 text-brand-500 rounded-md text-xs">
                                            {{ __('Completed') }}
                                        </div>
                                    @elseif ($transaction->canceled_at)
                                        <div
                                            class="w-fit px-2 py-0.5 bg-orange-500/30 border border-orange-500 text-orange-500 rounded-md text-xs">
                                            {{ __('Canceled') }}
                                        </div>
                                    @endif
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
                        @endforeach
                    @else
                        <tr>
                            <td class="column text-center" colspan="8">
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
                    @if ($transactionList->onFirstPage() === false)
                        <a href="{{ $transactionList->previousPageUrl() }}"
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
                    @if ($transactionList->currentPage() > 3)
                        <a href="{{ $transactionList->url(1) }}"
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                            aria-current="page">{{ 1 }}</a>
                    @endif

                    @if ($transactionList->currentPage() > 4)
                        <span
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500">...</span>
                    @endif

                    @foreach (range(1, $transactionList->lastPage()) as $page)
                        @if ($page >= $transactionList->currentPage() - 2 && $page <= $transactionList->currentPage() + 2)
                            <a href="{{ $transactionList->url($page) }}"
                                class="{{ $page === $transactionList->currentPage() ? 'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-indigo-600 border-indigo-500' : 'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700' }}"
                                aria-current="page">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($transactionList->currentPage() + 2 < $transactionList->lastPage() - 1)
                        <span
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500">...</span>
                    @endif

                    @if ($transactionList->currentPage() + 2 < $transactionList->lastPage())
                        <a href="{{ $transactionList->url($transactionList->lastPage()) }}"
                            class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                            aria-current="page">{{ $transactionList->lastPage() }}</a>
                    @endif
                </div>

                <!-- Next Page Link -->
                <div class="-mt-px flex w-0 flex-1 justify-end">
                    @if ($transactionList->hasMorePages())
                        <a href="{{ $transactionList->nextPageUrl() }}"
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
