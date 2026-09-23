<x-app-layout>
    <x-slot:title>Payroll Asisten</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Payroll Asisten</h1>
                <p>Lembur Rp{{ number_format($rate, 0, ',', '.') }}/jam · 45 menit = 1 jam · tanggal merah dihitung sejak masuk</p>
            </div>
            <form method="get" action="{{ route('assistant-payroll.index') }}" class="flex items-end gap-2">
                <div class="input-group !mb-0">
                    <input type="month" name="month" class="custom-input" value="{{ $month }}" onchange="this.form.submit()" />
                </div>
            </form>
        </section>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Total upah lembur</p>
                <p class="text-xl font-bold text-emerald-600">Rp{{ number_format($total_pay, 0, ',', '.') }}</p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Asisten tercatat</p>
                <p class="text-xl font-bold text-slate-900">{{ count($rows) }} orang</p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Tarif lembur</p>
                <p class="text-xl font-bold text-slate-900">Rp{{ number_format($rate, 0, ',', '.') }}/jam</p>
            </div>
        </div>

        <div class="content-card overflow-hidden">
            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">Asisten</th>
                            <th class="py-2 pr-4 text-right">Hari masuk</th>
                            <th class="py-2 pr-4 text-right">Jam reguler</th>
                            <th class="py-2 pr-4 text-right">Lembur (mnt)</th>
                            <th class="py-2 pr-4 text-right">Lembur (jam)</th>
                            <th class="py-2 text-right">Upah lembur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $r)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 font-semibold">{{ $r->name }}</td>
                                <td class="py-2.5 pr-4 text-right">{{ $r->days }}</td>
                                <td class="py-2.5 pr-4 text-right">{{ number_format($r->regular_minutes / 60, 1, ',', '.') }} jam</td>
                                <td class="py-2.5 pr-4 text-right">{{ $r->overtime_minutes }} mnt</td>
                                <td class="py-2.5 pr-4 text-right font-semibold">{{ $r->overtime_hours }} jam</td>
                                <td class="py-2.5 text-right font-bold text-emerald-600">Rp{{ number_format($r->overtime_pay, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-12">
                                <p class="text-sm text-slate-500">Belum ada data jam kerja bulan ini.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200 font-bold">
                            <td class="py-2.5 pr-4" colspan="5">Total</td>
                            <td class="py-2.5 text-right text-emerald-600">Rp{{ number_format($total_pay, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </main>
</x-app-layout>
