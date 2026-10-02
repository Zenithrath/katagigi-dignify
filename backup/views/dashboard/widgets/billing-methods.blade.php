<x-dashboard.panel
    data-widget="billing-methods"
    icon="credit-card"
    :title="__('dashboard.billing.title')"
    :subtitle="__('dashboard.billing.subtitle')"
>
    @php
        $totalAmount = (float) $widget->methods->sum('total_amount');
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach ($widget->methods as $method)
            @php
                $share = $totalAmount > 0 ? round(((float) $method->total_amount / $totalAmount) * 100) : 0;
                $methodLabel = strtolower($method->method) === 'lainnya'
                    ? __('dashboard.billing.method_other')
                    : ucfirst($method->method);
            @endphp
            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200/90 bg-slate-50/60 px-4 py-3.5">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-900 truncate">{{ $methodLabel }}</p>
                    <p class="text-xs text-slate-500">{{ number_format($method->total_count, 0, ',', '.') }} {{ __('dashboard.billing.transactions') }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold text-emerald-700">{{ \App\Helpers\GeneralHelper::floatToRupiah((float) $method->total_amount) }}</p>
                    <p class="text-[11px] font-semibold text-slate-400">{{ $share }}%</p>
                </div>
            </div>
        @endforeach
    </div>

    @if ($widget->methods->isEmpty())
        <div class="py-10 flex flex-col items-center justify-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-3 border border-emerald-100">
                <x-lucide-credit-card class="w-7 h-7" />
            </div>
            <p class="text-base font-bold text-slate-800">{{ __('dashboard.billing.empty_title') }}</p>
            <p class="text-xs text-slate-500 mt-1 max-w-md">{{ __('dashboard.billing.empty_hint') }}</p>
        </div>
    @endif
</x-dashboard.panel>
