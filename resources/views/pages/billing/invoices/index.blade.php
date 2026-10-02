<x-app-layout>
    <x-slot:title>Tagihan</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Tagihan</h1>
                <p>Tagihan visit (split baru) — nota lama tetap di menu Transaksi</p>
            </div>
        </section>

        <x-flash-alerts />

        <div class="content-card overflow-hidden">
            <form method="get" action="{{ route('invoices.index') }}" class="p-6 pb-0 flex flex-wrap gap-3 items-end">
                <div class="input-group !mb-0">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="custom-select">
                        <option value="">Semua</option>
                        @foreach (['DRAFT', 'ISSUED', 'PARTIALLY_PAID', 'PAID', 'VOID'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-submit !w-auto !px-6">Tampil</button>
            </form>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">Nomor</th>
                            <th class="py-2 pr-4">Pasien</th>
                            <th class="py-2 pr-4">Dokter</th>
                            <th class="py-2 pr-4">Total</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 font-semibold">{{ $invoice->number }}</td>
                                <td class="py-2.5 pr-4">{{ $invoice->patient->name ?? '-' }}</td>
                                <td class="py-2.5 pr-4">{{ $invoice->doctor->user->name ?? '-' }}</td>
                                <td class="py-2.5 pr-4 font-semibold">Rp{{ number_format($invoice->total, 0, ',', '.') }}</td>
                                <td class="py-2.5 pr-4">
                                    @php
                                        $badge = match ($invoice->status) {
                                            'PAID' => 'badge-success',
                                            'ISSUED', 'PARTIALLY_PAID' => 'badge-warning',
                                            'VOID' => 'badge-danger',
                                            default => 'badge-neutral',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $invoice->status }}</span>
                                </td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="text-sm text-brand-600 hover:text-brand-700">Buka</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-12">
                                <p class="text-sm text-slate-500">Belum ada tagihan. Buat dari visit SIGNED via tombol "Buat tagihan".</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($invoices->hasPages())
                <div class="p-6 pt-0">{{ $invoices->links() }}</div>
            @endif
        </div>
    </main>
</x-app-layout>
