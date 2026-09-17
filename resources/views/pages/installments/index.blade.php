<x-app-layout>
    <x-slot:title>{{ __('general.service.index._title') }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('general.installment.index._title') }}</h1>
                <p>{{ __('general.installment.index._subtitle') }}</p>
            </div>
        </section>

        <x-flash-alerts />

        <section class="table-content overflow-x-auto">
            <table cal>
                <thead>
                    {{--  id, tx_code, patient_name, patient_code, amount, status, total, paid, rest, due_date, step --}}
                    <tr>
                        <th scope="col" class="index-column">{{ __('general.table.number_no') }}</th>
                        <th scope="col" class="column">{{ __('general.installment.index.table.patient_data') }}</th>
                        <th scope="col" class="column">{{ __('general.installment.index.table.amount') }}</th>
                        <th scope="col" class="column">{{ __('general.installment.index.table.status') }}</th>
                        <th scope="col" class="column">{{ __('general.installment.index.table.total') }}</th>
                        <th scope="col" class="column">{{ __('general.installment.index.table.paid') }}</th>
                        <th scope="col" class="column">{{ __('general.installment.index.table.rest') }}</th>
                        <th scope="col" class="column">{{ __('general.installment.index.table.due_date') }}</th>
                        <th scope="col" class="column">{{ __('general.installment.index.table.steps') }}</th>
                        <th scope="col" class="action-column"><span class="sr-only"></span></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($installments as $installment)
                        <tr>
                            <td class="column">{{ $loop->iteration }}</td>
                            <td class="column">
                                <span class="flex flex-col gap-0">
                                    <a href="{{ route('installments.show', $installment->id) }}" class="font-bold">
                                        {{ $installment->patient_name }}
                                    </a>
                                    <span>{{ $installment->patient_code }}</span>
                                </span>
                            </td>
                            <td class="column">{{ number_format($installment->amount, 2, ',', '.') }}</td>
                            <td class="column">{{ __('general.phrases.' . $installment->status) }}</td>
                            <td class="column">{{ number_format($installment->total, 2, ',', '.') }}</td>
                            <td class="column">{{ number_format($installment->paid, 2, ',', '.') }}</td>
                            <td class="column">{{ number_format($installment->rest, 2, ',', '.') }}</td>
                            <td class="column">{{ $installment->due_date }}</td>
                            <td class="column">
                                <ul>
                                    @foreach ($installment->steps as $item)
                                        <li>{{ $getType($item->type, $item->step, $item->status) . ': ' . number_format($item->amount, 2, ',', '.') }}
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="column">
                                <div class="flex gap-2">
                                    {{-- detail --}}
                                    <a href="{{ route('installments.show', $installment->id) }}"
                                        class="clickable-primary py-1 px-2 rounded-md">
                                        {{ __('general.installment.index.action.detail') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">{{ __('general.phrases.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="mt-4">
            <nav class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">
                <div class="-mt-px flex w-0 flex-1">
                    @if ($pagination->page > 1)
                        <a href="{{ route('installments.index', ['page' => $pagination->page - 1]) }}"
                            class="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                            <svg class="mr-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M18 10a.75.75 0 01-.75.75H4.66l2.1 1.95a.75.75 0 11-1.02 1.1l-3.5-3.25a.75.75 0 010-1.1l3.5-3.25a.75.75 0 111.02 1.1l-2.1 1.95h12.59A.75.75 0 0118 10z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="hidden md:block">{{ __('Previous') }}</span>
                            </button>
                    @endif
                </div>
                <div class="md:-mt-px flex">
                    @if ($pagination->page - 3 >= 0)
                        <a href="{{ route('installments.index', ['page' => 1]) }}" @class([
                            'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700',
                        ])
                            aria-current="page">{{ 1 }}</a>
                    @endif

                    @if ($pagination->page - 3 > 0)
                        <span class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500"
                            aria-current="page">...</span>
                    @endif


                    @if ($pagination->total > 1)
                        @foreach (range(1, $pagination->last) as $page)
                            @if ($page > $pagination->page - 2 && $page < $pagination->page + 2)
                                <a href="{{ route('installments.index', ['page' => $page]) }}" @class([
                                    'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium',
                                    'text-indigo-600 border-indigo-500' => $page === $pagination->page,
                                    'text-gray-500 hover:text-gray-700' => $page !== $pagination->page,
                                ])
                                    aria-current="page">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif

                    @if ($pagination->page + 2 < $pagination->last)
                        <span class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500"
                            aria-current="page">...</span>
                    @endif

                    @if ($pagination->page + 2 <= $pagination->last)
                        <a href="{{ route('installments.index', ['page' => $pagination->last]) }}"
                            @class([
                                'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700',
                            ]) aria-current="page">{{ $pagination->last }}</a>
                    @endif
                </div>

                <div class="-mt-px flex w-0 flex-1 justify-end">
                    @if ($pagination->page < $pagination->last)
                        <a href="{{ route('installments.index', ['page' => $pagination->page + 1]) }}"
                            class="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                            <span class="hidden md:block">{{ __('Next') }}</span>
                            <svg class="ml-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M2 10a.75.75 0 01.75-.75h12.59l-2.1-1.95a.75.75 0 111.02-1.1l3.5 3.25a.75.75 0 010 1.1l-3.5 3.25a.75.75 0 11-1.02-1.1l2.1-1.95H2.75A.75.75 0 012 10z"
                                    clip-rule="evenodd" />s
                            </svg>
                        </a>
                    @endif
                </div>
            </nav>
        </section>
    </main>
</x-app-layout>
