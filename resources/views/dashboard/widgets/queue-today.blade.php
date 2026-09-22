<section data-widget="queue-today" class="rounded-[22px] bg-white p-6 sm:p-7 border border-slate-200/90 shadow-sm transition-all duration-300 hover:shadow-md">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-5 mb-5">
        <div>
            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                <x-lucide-clock class="w-5 h-5 text-emerald-600" />
                {{ __('dashboard.queue.title') }}
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('dashboard.queue.subtitle') }}</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
            <x-lucide-list-ordered class="w-3.5 h-3.5" /> {{ $widget->appointments->count() }} {{ __('dashboard.queue.count_suffix') }}
        </span>
    </div>

    @if ($widget->appointments->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="pb-3 pl-2">{{ __('dashboard.queue.th_time') }}</th>
                        <th class="pb-3">{{ __('dashboard.queue.th_patient') }}</th>
                        <th class="pb-3">{{ __('dashboard.queue.th_service') }}</th>
                        <th class="pb-3 text-right pr-2">{{ __('dashboard.queue.th_status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
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
                </tbody>
            </table>
        </div>
    @else
        <div class="py-12 flex flex-col items-center justify-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-3 border border-emerald-100">
                <x-lucide-check-circle-2 class="w-7 h-7" />
            </div>
            <p class="text-base font-bold text-slate-800">{{ __('dashboard.queue.empty_title') }}</p>
            <p class="text-xs text-slate-500 mt-1 max-w-md">{{ __('dashboard.queue.empty_hint') }}</p>
        </div>
    @endif
</section>
