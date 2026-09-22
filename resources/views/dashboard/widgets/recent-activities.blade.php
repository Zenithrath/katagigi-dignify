<section data-widget="recent-activities" class="rounded-[22px] bg-white p-6 sm:p-7 border border-slate-200/90 shadow-sm transition-all duration-300 hover:shadow-md">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-5 mb-5">
        <div>
            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                <x-lucide-receipt-text class="w-5 h-5 text-emerald-600" />
                {{ $widget->type === 'records' ? __('dashboard.recent.title_records') : __('dashboard.recent.title_transactions') }}
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ $widget->type === 'records' ? __('dashboard.recent.subtitle_records') : __('dashboard.recent.subtitle_transactions') }}
            </p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    @if ($widget->type === 'records')
                        <th class="pb-3 pl-2">{{ __('dashboard.recent.th_date') }}</th>
                        <th class="pb-3">{{ __('dashboard.recent.th_patient') }}</th>
                        <th class="pb-3">{{ __('dashboard.recent.th_diagnosis') }}</th>
                        <th class="pb-3 text-right pr-2">{{ __('dashboard.recent.th_total') }}</th>
                    @else
                        <th class="pb-3 pl-2">{{ __('dashboard.recent.th_code') }}</th>
                        <th class="pb-3">{{ __('dashboard.recent.th_patient') }}</th>
                        <th class="pb-3">{{ __('dashboard.recent.th_method') }}</th>
                        <th class="pb-3 text-right pr-2">{{ __('dashboard.recent.th_total') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @foreach ($widget->rows as $row)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        @if ($widget->type === 'records')
                            <td class="py-3.5 pl-2 font-mono text-xs font-semibold text-slate-500">
                                {{ \Carbon\Carbon::parse($row->appointment_date ?? $row->created_at)->locale(app()->getLocale())->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3.5 font-semibold text-slate-900">{{ $row->patient_name }}</td>
                            <td class="py-3.5 text-slate-600 truncate max-w-xs">{{ \Illuminate\Support\Str::limit($row->diagnosis ?? '—', 60) }}</td>
                            <td class="py-3.5 text-right pr-2 font-bold text-emerald-700">{{ \App\Helpers\GeneralHelper::floatToRupiah((float) $row->billing) }}</td>
                        @else
                            <td class="py-3.5 pl-2 font-mono text-xs font-semibold text-slate-500">#{{ $row->sequence }}</td>
                            <td class="py-3.5 font-semibold text-slate-900">{{ $row->patient_name }}</td>
                            <td class="py-3.5 text-slate-600">{{ ucfirst($row->payment_method ?? '—') }}</td>
                            <td class="py-3.5 text-right pr-2 font-bold text-emerald-700">{{ \App\Helpers\GeneralHelper::floatToRupiah((float) $row->billing) }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
