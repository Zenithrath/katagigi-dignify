<x-app-layout>
    <x-slot:title>Kwitansi {{ $payment->receipt->number ?? '' }}</x-slot:title>

    <main class="main-table-container">
        <div class="content-card p-8 max-w-2xl mx-auto">
            <div class="text-center border-b border-slate-200 pb-4 mb-4">
                <h1 class="text-xl font-bold text-slate-900">Kwitansi Pembayaran</h1>
                <p class="text-sm text-slate-500">Klinik Kata Gigi</p>
            </div>

            <dl class="detail-list">
                <div class="data-container">
                    <dt>No. Kwitansi</dt>
                    <dd class="font-bold">{{ $payment->receipt->number ?? '-' }}</dd>
                </div>
                <div class="data-container">
                    <dt>Tanggal</dt>
                    <dd>{{ $payment->paid_at?->format('d M Y H:i') }}</dd>
                </div>
                <div class="data-container">
                    <dt>Diterima dari</dt>
                    <dd class="font-semibold">{{ $payment->invoice->patient->name ?? '-' }}</dd>
                </div>
                <div class="data-container">
                    <dt>Tagihan</dt>
                    <dd>{{ $payment->invoice->number }}</dd>
                </div>
                <div class="data-container">
                    <dt>Jumlah</dt>
                    <dd class="font-bold text-emerald-600 text-lg">Rp{{ number_format($payment->amount, 0, ',', '.') }}</dd>
                </div>
                <div class="data-container">
                    <dt>Metode</dt>
                    <dd>{{ $payment->method }}</dd>
                </div>
                <div class="data-container">
                    <dt>Kasir</dt>
                    <dd>{{ $payment->receiver->name ?? '-' }}</dd>
                </div>
                @if ($payment->notes)
                    <div class="data-container">
                        <dt>Catatan</dt>
                        <dd>{{ $payment->notes }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-6 flex justify-end print:hidden">
                <button type="button" onclick="window.print()" class="clickable-primary px-5 py-2 rounded-xl">Cetak</button>
            </div>
        </div>
    </main>
</x-app-layout>
