<x-dashboard.panel
    data-widget="queue-today"
    icon="clock"
    :title="__('dashboard.queue.title')"
    :subtitle="__('dashboard.queue.subtitle')"
    :badge="$widget->appointments->count().' '.__('dashboard.queue.count_suffix')"
>
    <x-dashboard.data-table
        :empty="$widget->appointments->isEmpty()"
        empty-icon="check-circle-2"
        :empty-title="__('dashboard.queue.empty_title')"
        :empty-hint="__('dashboard.queue.empty_hint')"
    >
        <x-slot:head>
            <th class="pb-3 pl-2">{{ __('dashboard.queue.th_time') }}</th>
            <th class="pb-3">{{ __('dashboard.queue.th_patient') }}</th>
            <th class="pb-3">{{ __('dashboard.queue.th_service') }}</th>
            <th class="pb-3 text-right pr-2">{{ __('dashboard.queue.th_status') }}</th>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($widget->appointments as $appointment)
                @php
                    $services = json_decode($appointment->services ?? '[]', true);
                    $serviceName = collect(is_array($services) ? $services : [])->first()['name'] ?? '—';
                    $statusLabel = !empty($appointment->recorded_at)
                        ? __('dashboard.queue.status_done')
                        : (!empty($appointment->confirmed_at) ? __('dashboard.queue.status_confirmed') : __('dashboard.queue.status_waiting'));
                    $statusClass = !empty($appointment->recorded_at)
                        ? 'bg-slate-100 text-slate-600 border-slate-200'
                        : (!empty($appointment->confirmed_at) ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200');
                @endphp
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="py-3.5 pl-2 font-mono text-xs font-semibold text-slate-500">
                        {{ \Carbon\Carbon::parse($appointment->time_start)->format('H:i') }}–{{ \Carbon\Carbon::parse($appointment->time_end)->format('H:i') }}
                    </td>
                    <td class="py-3.5 font-semibold text-slate-900">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-100 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($appointment->patient_name, 0, 1)) }}
                            </div>
                            <span class="truncate max-w-xs">{{ $appointment->patient_name }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 text-slate-600">{{ $serviceName }}</td>
                    <td class="py-3.5 text-right pr-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-dashboard.data-table>
</x-dashboard.panel>
