<x-app-layout>
    <x-slot:title>Laporan Keuangan</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Laporan Keuangan</h1>
                <p>Tagihan, penerimaan, beban, dan produksi dokter</p>
            </div>
            <form method="get" action="{{ route('finance-report.index') }}" class="flex items-end gap-2">
                <div class="input-group !mb-0">
                    <input type="month" name="month" class="custom-input" value="{{ $month }}" onchange="this.form.submit()" />
                </div>
            </form>
        </section>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Ditagihkan</p>
                <p class="text-xl font-bold text-slate-900">Rp{{ number_format($invoiced, 0, ',', '.') }}</p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Terkumpul</p>
                <p class="text-xl font-bold text-emerald-600">Rp{{ number_format($collected, 0, ',', '.') }}</p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Piutang</p>
                <p class="text-xl font-bold text-amber-600">Rp{{ number_format($outstanding, 0, ',', '.') }}</p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Beban</p>
                <p class="text-xl font-bold text-red-600">Rp{{ number_format($expenses, 0, ',', '.') }}</p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Fee belum cair</p>
                <p class="text-xl font-bold text-slate-700">Rp{{ number_format($feesUnpaid, 0, ',', '.') }}</p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Bersih (terkumpul − beban)</p>
                <p class="text-xl font-bold {{ $net >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp{{ number_format($net, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="content-card p-6">
                <h3 class="font-bold text-slate-900 mb-2">Produksi per dokter</h3>
                @forelse ($perDoctor as $row)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
                        <span>{{ $row->doctor->user->name ?? '-' }} <span class="text-slate-400">({{ $row->invoice_count }} tagihan)</span></span>
                        <span class="font-semibold">Rp{{ number_format($row->total, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada tagihan bulan ini.</p>
                @endforelse
            </div>
            <div class="content-card p-6">
                <h3 class="font-bold text-slate-900 mb-2">Penerimaan per metode</h3>
                @forelse ($perMethod as $row)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
                        <span>{{ $row->method }} <span class="text-slate-400">({{ $row->count }}×)</span></span>
                        <span class="font-semibold">Rp{{ number_format($row->total, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada penerimaan bulan ini.</p>
                @endforelse
            </div>
        </div>
    </main>
</x-app-layout>
