<section data-widget="billing-methods" class="rounded-[22px] bg-white p-6 sm:p-7 border border-slate-200/90 shadow-sm transition-all duration-300 hover:shadow-md">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-5 mb-5">
        <div>
            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                <x-lucide-credit-card class="w-5 h-5 text-emerald-600" />
                {{ __('dashboard.billing.title') }}
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('dashboard.billing.subtitle') }}</p>
        </div>
    </div>

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
</section>
