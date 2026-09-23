<x-dashboard.panel
    data-widget="recent-activities"
    icon="receipt-text"
    :title="$widget->type === 'records' ? __('dashboard.recent.title_records') : __('dashboard.recent.title_transactions')"
    :subtitle="$widget->type === 'records' ? __('dashboard.recent.subtitle_records') : __('dashboard.recent.subtitle_transactions')"
>
    <x-dashboard.data-table
        :empty="$widget->rows->isEmpty()"
        :empty-icon="$widget->type === 'records' ? 'stethoscope' : 'receipt-text'"
        :empty-title="$widget->type === 'records' ? __('dashboard.recent.empty_records') : __('dashboard.recent.empty_transactions')"
    >
        <x-slot:head>
            @if ($widget->type === 'records')
                <th class="pb-3 pl-2">{{ __('dashboard.recent.th_date') }}</th>
                <th class="pb-3">{{ __('dashboard.recent.th_patient') }}</th>
                <th class="pb-3">{{ __('dashboard.recent.th_diagnosis') }}</th>
            @else
                <th class="pb-3 pl-2">{{ __('dashboard.recent.th_code') }}</th>
                <th class="pb-3">{{ __('dashboard.recent.th_patient') }}</th>
                <th class="pb-3">{{ __('dashboard.recent.th_method') }}</th>
            @endif
            <th class="pb-3 text-right pr-2">{{ __('dashboard.recent.th_total') }}</th>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($widget->rows as $row)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    @if ($widget->type === 'records')
                        <td class="py-3.5 pl-2 font-mono text-xs font-semibold text-slate-500">
                            {{ \Carbon\Carbon::parse($row->appointment_date ?? $row->created_at)->locale(app()->getLocale())->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-3.5 font-semibold text-slate-900">{{ $row->patient_name }}</td>
                        <td class="py-3.5 text-slate-600 truncate max-w-xs">{{ \Illuminate\Support\Str::limit($row->diagnosis ?? '—', 60) }}</td>
                    @else
                        <td class="py-3.5 pl-2 font-mono text-xs font-semibold text-slate-500">#{{ $row->sequence }}</td>
                        <td class="py-3.5 font-semibold text-slate-900">{{ $row->patient_name }}</td>
                        <td class="py-3.5 text-slate-600">{{ ucfirst($row->payment_method ?? '—') }}</td>
                    @endif
                    <td class="py-3.5 text-right pr-2 font-bold text-emerald-700">{{ \App\Helpers\GeneralHelper::floatToRupiah((float) $row->billing) }}</td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-dashboard.data-table>
</x-dashboard.panel>
