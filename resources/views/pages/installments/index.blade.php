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
            <table>
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
                                        class="clickable-primary py-1.5 px-3 rounded-lg text-xs font-bold">
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
            <nav class="flex items-center justify-between border-t border-slate-200 px-1 py-3">
                <div class="flex w-0 flex-1">
                    @if ($pagination->page > 1)
                        <a href="{{ route('installments.index', ['page' => $pagination->page - 1]) }}"
                            class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700 transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/></svg>
                            <span class="hidden md:block">{{ __('Previous') }}</span>
                        </a>
                    @endif
                </div>

                <div class="hidden md:flex items-center gap-1">
                    @if ($pagination->page - 3 >= 0)
                        <a href="{{ route('installments.index', ['page' => 1]) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">{{ 1 }}</a>
                    @endif
                    @if ($pagination->page - 3 > 0)
                        <span class="px-1 text-slate-400">...</span>
                    @endif
                    @if ($pagination->total > 1)
                        @foreach (range(1, $pagination->last) as $page)
                            @if ($page > $pagination->page - 2 && $page < $pagination->page + 2)
                                <a href="{{ route('installments.index', ['page' => $page]) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $page === $pagination->page ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                    @if ($pagination->page + 2 < $pagination->last)
                        <span class="px-1 text-slate-400">...</span>
                    @endif
                    @if ($pagination->page + 2 <= $pagination->last)
                        <a href="{{ route('installments.index', ['page' => $pagination->last]) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">{{ $pagination->last }}</a>
                    @endif
                </div>

                <div class="flex w-0 flex-1 justify-end">
                    @if ($pagination->page < $pagination->last)
                        <a href="{{ route('installments.index', ['page' => $pagination->page + 1]) }}"
                            class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700 transition-colors">
                            <span class="hidden md:block">{{ __('Next') }}</span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                        </a>
                    @endif
                </div>
            </nav>
        </section>
    </main>
</x-app-layout>
