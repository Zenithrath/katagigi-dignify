<x-app-layout>
    <x-slot:title>Perbandingan Pendapatan YoY</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Pendapatan Year-on-Year</h1>
                <p>Bandingkan pendapatan {{ $year }} vs {{ $prev }} per bulan</p>
            </div>
            <form method="get" action="{{ route('revenue-report.index') }}" class="flex items-end gap-2">
                <div class="input-group !mb-0">
                    <select name="year" class="custom-select" onchange="this.form.submit()">
                        @for ($y = (int) date('Y'); $y >= (int) date('Y') - 5; $y--)
                            <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </form>
        </section>

        @php
            $gInv = \App\Http\Controllers\Report\RevenueReportController::growth($total->invoiced, $total->invoiced_prev);
            $gCol = \App\Http\Controllers\Report\RevenueReportController::growth($total->collected, $total->collected_prev);
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Ditagihkan {{ $year }}</p>
                <p class="text-xl font-bold text-slate-900">Rp{{ number_format($total->invoiced, 0, ',', '.') }}</p>
                <p class="text-xs {{ ($gInv ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }} font-semibold">
                    {{ is_null($gInv) ? 'tahun pertama' : number_format($gInv, 1, ',', '.') . '% YoY' }}
                </p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Ditagihkan {{ $prev }}</p>
                <p class="text-xl font-bold text-slate-500">Rp{{ number_format($total->invoiced_prev, 0, ',', '.') }}</p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Terkumpul {{ $year }}</p>
                <p class="text-xl font-bold text-emerald-600">Rp{{ number_format($total->collected, 0, ',', '.') }}</p>
                <p class="text-xs {{ ($gCol ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }} font-semibold">
                    {{ is_null($gCol) ? 'tahun pertama' : number_format($gCol, 1, ',', '.') . '% YoY' }}
                </p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Terkumpul {{ $prev }}</p>
                <p class="text-xl font-bold text-slate-500">Rp{{ number_format($total->collected_prev, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="content-card p-6 mb-4" x-data="yoyChart(@js(['rows' => $rows, 'year' => $year, 'prev' => $prev]))">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h3 class="font-bold text-slate-900">Grafik bulanan</h3>
                <div class="flex gap-2">
                    <button type="button" @click="mode = 'collected'"
                        :class="mode === 'collected' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200'"
                        class="py-1.5 px-4 rounded-xl text-xs font-bold">Terkumpul</button>
                    <button type="button" @click="mode = 'invoiced'"
                        :class="mode === 'invoiced' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200'"
                        class="py-1.5 px-4 rounded-xl text-xs font-bold">Ditagihkan</button>
                </div>
            </div>
            <div class="relative h-[320px]"><canvas x-ref="yoyCanvas"></canvas></div>
        </div>

        <div class="content-card overflow-hidden">
            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">Bulan</th>
                            <th class="py-2 pr-4 text-right">Ditagihkan {{ $year }}</th>
                            <th class="py-2 pr-4 text-right">Ditagihkan {{ $prev }}</th>
                            <th class="py-2 pr-4 text-right">Terkumpul {{ $year }}</th>
                            <th class="py-2 pr-4 text-right">Terkumpul {{ $prev }}</th>
                            <th class="py-2 text-right">Tumbuh (terkumpul)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            @php $g = \App\Http\Controllers\Report\RevenueReportController::growth($r->collected, $r->collected_prev); @endphp
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 font-semibold">{{ DateTime::createFromFormat('!m', $r->month)->format('F') }}</td>
                                <td class="py-2.5 pr-4 text-right">Rp{{ number_format($r->invoiced, 0, ',', '.') }}</td>
                                <td class="py-2.5 pr-4 text-right text-slate-400">Rp{{ number_format($r->invoiced_prev, 0, ',', '.') }}</td>
                                <td class="py-2.5 pr-4 text-right">Rp{{ number_format($r->collected, 0, ',', '.') }}</td>
                                <td class="py-2.5 pr-4 text-right text-slate-400">Rp{{ number_format($r->collected_prev, 0, ',', '.') }}</td>
                                <td class="py-2.5 text-right font-bold {{ is_null($g) ? 'text-slate-400' : ($g >= 0 ? 'text-emerald-600' : 'text-red-600') }}">
                                    {{ is_null($g) ? '—' : number_format($g, 1, ',', '.') . '%' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</x-app-layout>
