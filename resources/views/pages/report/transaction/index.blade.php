<x-app-layout>
    <x-slot:title>{{ __('report.transaction.index._title') }}</x-slot:title>

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

        <div class="content-card">
            <form action="{{ route('transactions.index') }}" method="GET" class="flex flex-col gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1.5 block">{{ __('report.transaction.index.labels.date_start') }}</label>
                        <input type="date" name="since" value="{{ request('since') }}" class="custom-input" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1.5 block">{{ __('report.transaction.index.labels.date_end') }}</label>
                        <input type="date" name="until" value="{{ request('until') }}" class="custom-input" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1.5 block">{{ __('report.transaction.index.labels.patient_keyword') }}</label>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="{{ __('report.transaction.index.placeholders.type_here') }}" class="custom-input" />
                        <small class="text-xs text-slate-400 mt-1 block">{{ __('report.transaction.index.helpers.patient_keyword') }}</small>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="clickable-primary py-2.5 px-6 rounded-xl text-sm font-bold" type="submit">{{ __('report.transaction.index.actions.find') }}</button>
                </div>
            </form>
        </div>

        <div class="flex items-center gap-2 mt-1 mb-2">
            <span class="text-sm text-slate-500">Menampilkan <span class="font-bold text-slate-700">{{ $transactionList->total() }}</span> transaksi</span>
        </div>

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
                                        <span class="badge badge-success"><span class="dot"></span> {{ __('Completed') }}</span>
                                    @else
                                        <span class="badge badge-warning"><span class="dot"></span> {{ __('Canceled') }}</span>
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

        <x-table-paginator :paginator="$transactionList" />
    </main>
</x-app-layout>
