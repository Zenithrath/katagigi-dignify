<x-dashboard.panel
    data-widget="patients-incomplete"
    icon="alert-circle"
    badge-tone="amber"
    :title="__('dashboard.incomplete.title')"
    :subtitle="__('dashboard.incomplete.hint')"
    :badge="__('dashboard.incomplete.count_badge', ['count' => number_format($widget->count, 0, ',', '.')])"
>
    <x-dashboard.data-table
        :empty="$widget->patients->isEmpty()"
        empty-icon="user-check"
        :empty-title="__('dashboard.incomplete.empty_title')"
        :empty-hint="__('dashboard.incomplete.empty_hint')"
    >
        <x-slot:head>
            <th class="pb-3 pl-2">{{ __('dashboard.incomplete.th_code') }}</th>
            <th class="pb-3">{{ __('dashboard.incomplete.th_patient') }}</th>
            <th class="pb-3">{{ __('dashboard.incomplete.th_missing') }}</th>
            <th class="pb-3 text-right pr-2">{{ __('dashboard.incomplete.th_action') }}</th>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($widget->patients as $patient)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="py-3.5 pl-2 font-mono text-xs font-semibold text-slate-500">
                        {{ $patient->code }}
                    </td>
                    <td class="py-3.5 font-semibold text-slate-900">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-100 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($patient->name, 0, 1)) }}
                            </div>
                            <span class="truncate max-w-xs">{{ $patient->name }}</span>
                        </div>
                    </td>
                    <td class="py-3.5">
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($patient->missing_fields as $field)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200/70">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ $field }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="py-3.5 text-right pr-2">
                        <a href="{{ route('patients.edit', $patient->id) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 hover:border-emerald-300 transition-colors">
                            {{ __('dashboard.incomplete.action') }} <x-lucide-arrow-up-right class="w-3.5 h-3.5" />
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-dashboard.data-table>

    <x-slot:footer>
        <span>{{ __('dashboard.incomplete.footer') }}</span>
        <a href="{{ route('patients.index') }}" class="font-semibold text-emerald-800 hover:text-emerald-900 inline-flex items-center gap-1">
            {{ __('dashboard.incomplete.see_all') }} <x-lucide-arrow-right class="w-3.5 h-3.5" />
        </a>
    </x-slot:footer>
</x-dashboard.panel>
