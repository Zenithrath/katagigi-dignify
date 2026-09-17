<x-app-layout>
    <x-slot:title>Salaries Report</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Salaries Report</h1>
                <p>Doctor salary calculations and breakdown</p>
            </div>
        </section>

        <x-flash-alerts />

        <div class="content-card">
            <form action="" method="GET" class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="text-xs font-semibold text-slate-600 mb-1.5 block">Start Date</label>
                    <input type="date" name="start_date" value="{{ $start_date }}" class="custom-input" />
                </div>
                <div class="flex-1">
                    <label class="text-xs font-semibold text-slate-600 mb-1.5 block">End Date</label>
                    <input type="date" name="end_date" value="{{ $end_date }}" class="custom-input" />
                </div>
                <button type="submit" class="btn-submit !w-auto !px-6">
                    <x-lucide-search class="w-4 h-4" />
                    Submit
                </button>
            </form>
        </div>

        {{-- Summary Table --}}
        <div class="content-card">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <x-lucide-calculator class="w-4 h-4 text-emerald-500" />
                Salary Summary
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">Doctor Name</th>
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">SIP</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">Regular</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">Share %</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">Share</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">Over Prod</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">OP %</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">OP Share</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">Rontgent</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">R. %</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">R. Share</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">Shifts</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">Shift Fee</th>
                            <th class="text-right py-3 px-3 font-bold text-slate-900">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_regular = 0;
                            $total_overproduction = 0;
                            $total_rontgent = 0;
                            $total_shift_fee = 0;
                            $total_share = 0;
                        @endphp

                        @foreach ($doctors as $dx)
                            @php
                                $total_regular += $dx->regular_share;
                                $total_overproduction += $dx->overproduction_share;
                                $total_rontgent += $dx->rontgent_share;
                                $total_shift_fee += $dx->shift_fee;
                                $total_share += $dx->share_total;
                            @endphp

                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-3 font-medium text-slate-900">{{ $dx->doctor->name }}</td>
                                <td class="py-3 px-3">
                                    @if (!is_null($dx->doctor->niptk) || $dx->doctor->niptk != '')
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            OK
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-red-700 bg-red-50 px-2 py-0.5 rounded-lg">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            NO
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right text-slate-600">{{ number_format($dx->regular_income, 2, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right text-slate-600">{{ $dx->regular_percentage . '%' }}</td>
                                <td class="py-3 px-3 text-right font-medium text-slate-900">{{ number_format($dx->regular_share, 2, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right text-slate-600">{{ number_format($dx->overproduction_income, 2, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right text-slate-600">{{ $dx->overproduction_percentage . '%' }}</td>
                                <td class="py-3 px-3 text-right font-medium text-slate-900">{{ number_format($dx->overproduction_share, 2, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right text-slate-600">{{ number_format($dx->rontgent_income, 2, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right text-slate-600">{{ $dx->rontgent_percentage . '%' }}</td>
                                <td class="py-3 px-3 text-right font-medium text-slate-900">{{ number_format($dx->rontgent_share, 2, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right text-slate-600">{{ $dx->shifts }}</td>
                                <td class="py-3 px-3 text-right text-slate-600">{{ number_format($dx->shift_fee, 2, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right font-bold text-emerald-600">{{ number_format($dx->share_total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-bold">
                            <td colspan="2" class="py-3 px-3 text-right text-slate-700">TOTAL</td>
                            <td class="py-3 px-3 text-right text-slate-900">{{ number_format($total_regular, 2, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center text-slate-400">---</td>
                            <td class="py-3 px-3 text-right text-slate-900">{{ number_format($total_regular, 2, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center text-slate-400">---</td>
                            <td class="py-3 px-3 text-center text-slate-400">---</td>
                            <td class="py-3 px-3 text-right text-slate-900">{{ number_format($total_overproduction, 2, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center text-slate-400">---</td>
                            <td class="py-3 px-3 text-center text-slate-400">---</td>
                            <td class="py-3 px-3 text-right text-slate-900">{{ number_format($total_rontgent, 2, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-slate-900">{{ number_format($total_shift_fee, 2, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-emerald-600">{{ number_format($total_share, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Regular & Overproduction Transactions --}}
        <div class="content-card">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <x-lucide-stethoscope class="w-4 h-4 text-emerald-500" />
                Regular & Overproduction Transactions
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">Tx ID</th>
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">Tx Number</th>
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">Type</th>
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">Appointment Time</th>
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">Svc Name</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">Income</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_service = 0;
                            $total_regular = 0;
                        @endphp

                        @foreach ($doctors as $dx)
                            <tr class="bg-emerald-50/50">
                                <td colspan="5" class="py-2 px-3 font-bold text-slate-900">{{ $dx->doctor->name }}</td>
                                <td class="py-2 px-3 text-right font-medium text-emerald-700">
                                    {{ number_format($dx->regular_income + $dx->overproduction_income, 2, ',', '.') }}</td>
                            </tr>

                            @foreach ($dx->non_rontgen_transactions as $tx)
                                @php
                                    $total_service += 1;
                                    $total_regular += $tx->undiscount - $tx->discount;
                                @endphp

                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                    <td class="py-2.5 px-3 text-slate-600">{{ $tx->id }}</td>
                                    <td class="py-2.5 px-3 text-slate-600">{{ $tx->num }}</td>
                                    <td class="py-2.5 px-3">
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $tx->is_overproduction == 1 ? 'text-amber-700 bg-amber-50' : 'text-emerald-700 bg-emerald-50' }} px-2 py-0.5 rounded-lg">
                                            {{ $tx->is_overproduction == 1 ? 'OVERPRODUCTION' : 'REGULAR' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-600">{{ $tx->appointment_datetime }}</td>
                                    <td class="py-2.5 px-3 text-slate-900 font-medium">{{ $tx->svc_name }}</td>
                                    <td class="py-2.5 px-3 text-right font-medium text-slate-900">
                                        {{ number_format($tx->undiscount - $tx->discount, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-bold">
                            <td colspan="3" class="py-3 px-3 text-right text-slate-700">Total</td>
                            <td class="py-3 px-3 text-right text-slate-600">{{ number_format($total_service, 0, ',', '.') . ' Services' }}</td>
                            <td class="py-3 px-3 text-right text-slate-900">{{ number_format($total_regular, 2, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-slate-900"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Rontgen Transactions --}}
        <div class="content-card">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <x-lucide-scan class="w-4 h-4 text-emerald-500" />
                Rontgen Transactions
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">Tx ID</th>
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">Tx Number</th>
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">Type</th>
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">Appointment Time</th>
                            <th class="text-left py-3 px-3 font-semibold text-slate-600">Svc Name</th>
                            <th class="text-right py-3 px-3 font-semibold text-slate-600">Income</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_service = 0;
                            $total_rontgent = 0;
                        @endphp

                        @foreach ($doctors as $dx)
                            <tr class="bg-blue-50/50">
                                <td colspan="5" class="py-2 px-3 font-bold text-slate-900">{{ $dx->doctor->name }}</td>
                                <td class="py-2 px-3 text-right font-medium text-blue-700">{{ number_format($dx->rontgent_income, 2, ',', '.') }}</td>
                            </tr>

                            @foreach ($dx->rontgen_transactions as $tx)
                                @php
                                    $total_service += 1;
                                    $total_rontgent += $tx->undiscount - $tx->discount;
                                @endphp

                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                    <td class="py-2.5 px-3 text-slate-600">{{ $tx->id }}</td>
                                    <td class="py-2.5 px-3 text-slate-600">{{ $tx->num }}</td>
                                    <td class="py-2.5 px-3">
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg">
                                            RONTGEN
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-600">{{ $tx->appointment_datetime }}</td>
                                    <td class="py-2.5 px-3 text-slate-900 font-medium">{{ $tx->svc_name }}</td>
                                    <td class="py-2.5 px-3 text-right font-medium text-slate-900">
                                        {{ number_format($tx->undiscount - $tx->discount, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-bold">
                            <td colspan="3" class="py-3 px-3 text-right text-slate-700">Total</td>
                            <td class="py-3 px-3 text-right text-slate-600">{{ number_format($total_service, 0, ',', '.') . ' Services' }}</td>
                            <td class="py-3 px-3 text-right text-slate-900">{{ number_format($total_rontgent, 2, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-slate-900"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </main>
</x-app-layout>
