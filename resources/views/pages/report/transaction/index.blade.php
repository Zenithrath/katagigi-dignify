<x-app-layout>
    <x-slot:title>{{ __('report.transaction.index._title') }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('report.transaction.index._title') }}</h1>
                <p>{{ __('report.transaction.index._subtitle') }}</p>
            </div>

            {{-- @can('create transaction') --}}
            @role('manajemen|nurse|admin')
                <a href="{{ route('transactions.create') }}" class="clickable-primary py-2 px-4 rounded-md">
                    {{ __('report.transaction.index.actions.add') }}
                </a>
            @endrole
            {{-- @endcan --}}
        </section>

        <div class="flex gap-2 mt-4 flex-wrap">
            <a href="{{ route('transactions.index') }}"
                class="px-4 py-2 rounded-xl text-sm font-semibold {{ ($tab ?? 'all') === 'all' ? 'clickable-primary' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua Nota</a>
            <a href="{{ route('transactions.index', ['tab' => 'pending']) }}"
                class="px-4 py-2 rounded-xl text-sm font-semibold {{ ($tab ?? 'all') === 'pending' ? 'clickable-primary' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Menunggu Pembayaran</a>
        </div>

        @if (($tab ?? 'all') === 'pending')
            <div class="content-card overflow-hidden mt-4">
                <div class="p-6 pb-2">
                    <h3 class="font-bold text-slate-900">Belum Bayar <span class="text-xs font-normal text-slate-500">tagihan terbit, lunasi di sini</span></h3>
                </div>
                <div class="p-6 pt-2 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 border-b border-slate-200">
                                <th class="py-2 pr-4">Tagihan</th>
                                <th class="py-2 pr-4">Pasien</th>
                                <th class="py-2 pr-4">Total / Sisa</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingInvoices as $invoice)
                                <tr class="border-b border-slate-100 last:border-0">
                                    <td class="py-2.5 pr-4">
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="font-semibold text-brand-600 hover:text-brand-700">{{ $invoice->number }}</a>
                                        <span class="text-xs text-slate-400 ml-1">{{ $invoice->visit->visit_number ?? '' }}</span>
                                    </td>
                                    <td class="py-2.5 pr-4">{{ $invoice->patient->name ?? '-' }}</td>
                                    <td class="py-2.5 pr-4">
                                        <span class="font-semibold">Rp{{ number_format($invoice->total, 0, ',', '.') }}</span>
                                        <span class="text-xs text-red-600 font-semibold ml-1">sisa Rp{{ number_format($invoice->amountDue(), 0, ',', '.') }}</span>
                                    </td>
                                    <td class="py-2.5 pr-4">
                                        <span class="badge {{ $invoice->status === 'PARTIALLY_PAID' ? 'badge-warning' : 'badge-danger' }}">
                                            {{ $invoice->status === 'PARTIALLY_PAID' ? 'Sebagian' : 'Belum Bayar' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-right">
                                        <form action="{{ route('invoices.payments.store', $invoice->id) }}" method="post" class="inline-flex items-center gap-1 justify-end">
                                            @csrf
                                            <input type="hidden" name="amount" value="{{ $invoice->amountDue() }}" />
                                            <select name="method" class="custom-select !py-1 !px-2 !text-xs !w-auto">
                                                @foreach (\App\Models\InvoicePayment::METHODS as $method)
                                                    <option value="{{ $method }}">{{ $method }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700"
                                                @click.prevent="if(confirm('Lunasi Rp{{ number_format($invoice->amountDue(), 0, ',', '.') }}?')) $el.closest('form').submit()">Tandai Lunas</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-8">
                                    <p class="text-sm text-slate-500">Tidak ada tunggakan. Semua lunas.</p>
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($pendingInvoices->hasPages())
                    <div class="p-6 pt-0">{{ $pendingInvoices->links() }}</div>
                @endif
            </div>

            <div class="content-card overflow-hidden mt-4">
                <div class="p-6 pb-2">
                    <h3 class="font-bold text-slate-900">Selesai diperiksa, belum ada tagihan</h3>
                </div>
                <div class="p-6 pt-2 overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody>
                            @forelse ($unbilledVisits as $visit)
                                <tr class="border-b border-slate-100 last:border-0">
                                    <td class="py-2.5 pr-4 font-semibold">{{ $visit->visit_number }}</td>
                                    <td class="py-2.5 pr-4">{{ $visit->patient->name ?? '-' }} <span class="text-xs text-slate-400">{{ $visit->doctor->user->name ?? '' }}</span></td>
                                    <td class="py-2.5 text-right">
                                        <form action="{{ route('visits.invoice.store', $visit->id) }}" method="post">
                                            @csrf
                                            <button type="submit" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Buatkan tagihan</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="text-center py-8">
                                    <p class="text-sm text-slate-500">Kosong.</p>
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
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
        @endif
    </main>
</x-app-layout>
