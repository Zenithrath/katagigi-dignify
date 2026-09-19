<x-app-layout>
    <x-slot:title>Tagihan {{ $invoice->number }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Tagihan {{ $invoice->number }}</h1>
                <p>{{ $invoice->patient->name ?? '-' }} — {{ $invoice->doctor->user->name ?? '-' }}</p>
            </div>
            <span class="badge {{ $invoice->status === 'PAID' ? 'badge-success' : ($invoice->status === 'VOID' ? 'badge-danger' : ($invoice->status === 'DRAFT' ? 'badge-neutral' : 'badge-warning')) }}">{{ $invoice->status }}</span>
        </section>

        <x-flash-alerts />

        <div class="content-card p-8">
            <dl class="detail-list">
                <div class="data-container">
                    <dt>Pasien</dt>
                    <dd class="font-semibold">{{ $invoice->patient->name ?? '-' }}</dd>
                </div>
                @if ($invoice->visit)
                    <div class="data-container">
                        <dt>Visit</dt>
                        <dd>
                            <a href="{{ route('visits.show', $invoice->visit_id) }}" class="text-brand-600 hover:text-brand-700">{{ $invoice->visit->visit_number }}</a>
                        </dd>
                    </div>
                @endif
                <div class="data-container">
                    <dt>Subtotal</dt>
                    <dd>Rp{{ number_format($invoice->subtotal, 0, ',', '.') }}</dd>
                </div>
                <div class="data-container">
                    <dt>Diskon / Pajak</dt>
                    <dd>Rp{{ number_format($invoice->discount, 0, ',', '.') }} / Rp{{ number_format($invoice->tax, 0, ',', '.') }}</dd>
                </div>
                <div class="data-container">
                    <dt>Total</dt>
                    <dd class="font-bold text-emerald-600 text-lg">Rp{{ number_format($invoice->total, 0, ',', '.') }}</dd>
                </div>
            </dl>

            <h3 class="font-bold text-slate-900 mt-6 mb-2">Item ({{ $invoice->items->count() }})</h3>
            <ul class="divide-y divide-slate-100">
                @foreach ($invoice->items as $item)
                    <li class="py-2 flex items-center justify-between gap-3 text-sm">
                        <span>
                            <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 mr-1">{{ $item->item_type }}</span>
                            @if ($item->tooth_fdi)<span class="font-bold">{{ $item->tooth_fdi }}</span>@endif
                            {{ $item->description }}
                            <span class="text-slate-500">· {{ $item->quantity }} × Rp{{ number_format($item->unit_price, 0, ',', '.') }}</span>
                        </span>
                        @if ($invoice->isEditable())
                            @can('update transaction')
                                <form action="{{ route('invoices.items.destroy', [$invoice->id, $item->id]) }}" method="post"
                                    @submit.prevent="if(confirm('Hapus item ini?')) $el.submit()">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                </form>
                            @endcan
                        @endif
                    </li>
                @endforeach
            </ul>

            @if ($invoice->isEditable())
                @can('update transaction')
                    <form method="post" action="{{ route('invoices.items.store', $invoice->id) }}" class="mt-3 grid grid-cols-1 md:grid-cols-5 gap-2">
                        @csrf
                        <select name="item_type" class="custom-select">
                            <option value="MEDICINE">Obat</option>
                            <option value="OTHER">Lainnya</option>
                        </select>
                        <input type="text" name="description" class="custom-input md:col-span-2" placeholder="Deskripsi *" required />
                        <input type="number" name="quantity" class="custom-input" value="1" min="1" />
                        <input type="number" name="unit_price" class="custom-input" value="0" min="0" />
                        <button type="submit" class="clickable-primary px-4 py-2 rounded-xl text-sm md:col-span-5">+ Item</button>
                    </form>
                    <form method="post" action="{{ route('invoices.issue', $invoice->id) }}" class="mt-4 flex flex-wrap items-end gap-2">
                        @csrf
                        <div class="input-group !mb-0">
                            <label for="discount">Diskon (Rp)</label>
                            <input type="number" name="discount" id="discount" class="custom-input" value="0" min="0" />
                        </div>
                        <div class="input-group !mb-0">
                            <label for="tax">Pajak (Rp)</label>
                            <input type="number" name="tax" id="tax" class="custom-input" value="0" min="0" />
                        </div>
                        <button type="submit" class="btn-submit !w-auto !px-8">Terbitkan tagihan</button>
                    </form>
                    <form method="post" action="{{ route('invoices.void', $invoice->id) }}" class="mt-2"
                        @submit.prevent="if(confirm('Batalkan tagihan ini?')) $el.submit()">
                        @csrf
                        <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Batalkan (VOID)</button>
                    </form>
                @endcan
            @endif

            <section class="mt-6 pt-6 border-t border-slate-200">
                <h3 class="font-bold text-slate-900 mb-1">Pembayaran</h3>
                <p class="text-sm text-slate-600 mb-3">
                    Dibayar: <strong>Rp{{ number_format($invoice->amountPaid(), 0, ',', '.') }}</strong>
                    · Sisa: <strong>Rp{{ number_format($invoice->amountDue(), 0, ',', '.') }}</strong>
                </p>
                @if ($invoice->payments->isNotEmpty())
                    <ul class="divide-y divide-slate-100 mb-4">
                        @foreach ($invoice->payments as $payment)
                            <li class="py-2 flex items-center justify-between gap-3 text-sm">
                                <span>
                                    <span class="font-semibold">Rp{{ number_format($payment->amount, 0, ',', '.') }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 ml-1">{{ $payment->method }}</span>
                                    <span class="text-slate-400 text-xs ml-1">{{ $payment->paid_at?->format('d M Y H:i') }}</span>
                                </span>
                                <a href="{{ route('payments.receipt', $payment->id) }}" target="_blank" class="text-sm text-brand-600 hover:text-brand-700">
                                    Kwitansi {{ $payment->receipt->number ?? '' }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @if (in_array($invoice->status, ['ISSUED', 'PARTIALLY_PAID'], true))
                    @can('create transaction')
                        <form method="post" action="{{ route('invoices.payments.store', $invoice->id) }}" class="grid grid-cols-1 md:grid-cols-4 gap-2">
                            @csrf
                            <div class="input-group">
                                <label for="amount">Nominal (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" name="amount" id="amount" class="custom-input" min="1" max="{{ (int) $invoice->amountDue() }}" value="{{ (int) $invoice->amountDue() }}" required />
                                @error('amount')<small class="danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="input-group">
                                <label for="method">Metode <span class="text-red-500">*</span></label>
                                <select name="method" id="method" class="custom-select">
                                    @foreach (\App\Models\InvoicePayment::METHODS as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-group">
                                <label for="pay_notes">Catatan</label>
                                <input type="text" name="notes" id="pay_notes" class="custom-input" />
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="btn-submit !w-auto !px-8">Bayar</button>
                            </div>
                        </form>
                    @endcan
                @endif
            </section>
        </div>
    </main>
</x-app-layout>
