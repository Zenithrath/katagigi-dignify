<section data-widget="kpi-row" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
    {{-- Kartu 1: Pendapatan (emerald 3D) — klinik untuk admin, pribadi untuk dokter --}}
    <div class="card-shiny-emerald rounded-[22px] p-6 cursor-default">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-100">
                {{ $widget->scoped ? __('dashboard.kpi.revenue_own') : __('dashboard.kpi.revenue_clinic') }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-semibold text-white backdrop-blur-sm border border-white/10">
                {{ __('dashboard.kpi.this_month') }}
            </span>
        </div>
        <p class="mt-4 text-3xl font-extrabold tracking-tight text-white">{{ $widget->revenue }}</p>
        <div class="mt-3 flex items-center gap-1.5 text-xs text-emerald-50/90">
            <x-lucide-check-circle-2 class="w-3.5 h-3.5 text-emerald-200 shrink-0" />
            <span class="truncate">
                {{ __('dashboard.kpi.from_notes', ['count' => number_format($widget->transactions, 0, ',', '.')]) }}
            </span>
        </div>
    </div>

    @if ($widget->patients !== null)
        {{-- Kartu 2: Total pasien terdaftar (admin) --}}
        <div class="rounded-[22px] bg-white p-6 border border-slate-200/90 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-lg hover:border-slate-300 relative overflow-hidden cursor-default">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                    <x-lucide-users class="w-5 h-5" />
                </div>
                <span class="inline-flex items-center text-[11px] font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full">
                    {{ __('dashboard.kpi.database') }}
                </span>
            </div>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('dashboard.kpi.patients_total') }}</p>
            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($widget->patients, 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-500">{{ __('dashboard.kpi.patients_total_hint') }}</p>
        </div>
    @endif

    @if ($widget->new_patients !== null)
        {{-- Kartu 3: Pasien baru bulan ini (admin) --}}
        <div class="rounded-[22px] bg-white p-6 border border-slate-200/90 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-lg hover:border-slate-300 relative overflow-hidden cursor-default">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                    <x-lucide-user-plus class="w-5 h-5" />
                </div>
                <span class="inline-flex items-center text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/70 px-2.5 py-0.5 rounded-full">
                    +{{ number_format($widget->new_patients, 0, ',', '.') }} {{ __('dashboard.kpi.new_badge') }}
                </span>
            </div>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('dashboard.kpi.patients_new') }}</p>
            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($widget->new_patients, 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-500">{{ __('dashboard.kpi.patients_new_hint') }}</p>
        </div>
    @endif

    @if ($widget->medical_records !== null)
        {{-- Kartu 4: Kunjungan selesai / rekam medis bulan ini --}}
        <div class="rounded-[22px] bg-white p-6 border border-slate-200/90 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-lg hover:border-slate-300 relative overflow-hidden cursor-default">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700">
                    <x-lucide-user-check class="w-5 h-5" />
                </div>
                <span class="inline-flex items-center text-[11px] font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full">
                    {{ __('dashboard.kpi.this_month') }}
                </span>
            </div>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('dashboard.kpi.visits') }}</p>
            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">
                {{ number_format($widget->transactions, 0, ',', '.') }}
                <span class="text-sm font-semibold text-slate-400">{{ __('dashboard.kpi.visits_unit') }}</span>
            </p>
            <p class="mt-2 text-xs text-slate-500">{{ __('dashboard.kpi.visits_hint') }}</p>
        </div>
    @endif

    @if ($widget->patients === null && $widget->medical_records === null)
        {{-- Perawat: kartu ringkas rekam medis --}}
        <div class="rounded-[22px] bg-white p-6 border border-slate-200/90 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-lg hover:border-slate-300 relative overflow-hidden cursor-default">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600">
                    <x-lucide-clipboard-list class="w-5 h-5" />
                </div>
                <span class="inline-flex items-center text-[11px] font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full">
                    {{ __('dashboard.kpi.this_month') }}
                </span>
            </div>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('dashboard.kpi.records') }}</p>
            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($widget->medical_records ?? 0, 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-500">{{ __('dashboard.kpi.records_hint') }}</p>
        </div>
    @endif
</section>
