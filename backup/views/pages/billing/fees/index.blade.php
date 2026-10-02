<x-app-layout>
    <x-slot:title>Fee Dokter</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Fee Dokter</h1>
                <p>Jasa medis terposting otomatis dari tagihan lunas</p>
            </div>
        </section>

        <x-flash-alerts />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Belum dicairkan</p>
                <p class="text-2xl font-bold text-amber-600">Rp{{ number_format($totalUnpaid, 0, ',', '.') }}</p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Sudah dicairkan</p>
                <p class="text-2xl font-bold text-emerald-600">Rp{{ number_format($totalPaid, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="content-card overflow-hidden">
            <form method="get" action="{{ route('doctor-fees.index') }}" class="p-6 pb-0 flex flex-wrap gap-3 items-end">
                <div class="input-group !mb-0">
                    <label for="month">Bulan</label>
                    <input type="month" name="month" id="month" class="custom-input" value="{{ request('month') }}" />
                </div>
                <div class="input-group !mb-0">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="custom-select">
                        <option value="">Semua</option>
                        <option value="UNPAID" @selected(request('status') === 'UNPAID')>UNPAID</option>
                        <option value="PAID" @selected(request('status') === 'PAID')>PAID</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit !w-auto !px-6">Tampil</button>
            </form>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">Dokter</th>
                            <th class="py-2 pr-4">Tagihan</th>
                            <th class="py-2 pr-4">Basis</th>
                            <th class="py-2 pr-4">Fee</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($fees as $fee)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 font-medium">{{ $fee->doctor->user->name ?? '-' }}</td>
                                <td class="py-2.5 pr-4">
                                    <a href="{{ route('invoices.show', $fee->invoice_id) }}" class="text-brand-600 hover:text-brand-700">{{ $fee->invoice->number ?? '-' }}</a>
                                </td>
                                <td class="py-2.5 pr-4">Rp{{ number_format($fee->base_amount, 0, ',', '.') }} ({{ rtrim(rtrim(number_format($fee->percentage, 2), '0'), '.') }}%)</td>
                                <td class="py-2.5 pr-4 font-semibold">Rp{{ number_format($fee->fee_amount, 0, ',', '.') }}</td>
                                <td class="py-2.5 pr-4">
                                    <span class="badge {{ $fee->status === 'PAID' ? 'badge-success' : 'badge-warning' }}">{{ $fee->status }}</span>
                                </td>
                                <td class="py-2.5 text-right">
                                    @if ($fee->status === 'UNPAID')
                                        @can('manage doctor fee')
                                            <form action="{{ route('doctor-fees.pay', $fee->id) }}" method="post">
                                                @csrf
                                                <button type="submit" class="text-sm text-brand-600 hover:text-brand-700">Cairkan</button>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-12">
                                <p class="text-sm text-slate-500">Belum ada fee. Fee terposting saat tagihan lunas.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($fees->hasPages())
                <div class="p-6 pt-0">{{ $fees->links() }}</div>
            @endif
        </div>
    </main>
</x-app-layout>
