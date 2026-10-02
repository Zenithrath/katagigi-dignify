<section data-widget="chart-trend" class="rounded-[22px] bg-white p-6 sm:p-7 border border-slate-200/90 shadow-sm transition-all duration-300 hover:shadow-md"
     x-data="revenueChartComponent(@js($widget->analytics))">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                <x-lucide-trending-up class="w-5 h-5 text-emerald-600" />
                {{ __('dashboard.chart.title') }}
            </h3>
            {{-- Headline total + % vs periode sebelumnya --}}
            <div class="flex items-end mt-3">
                <h3 class="text-emerald-700 leading-5 text-lg md:text-2xl font-bold" x-text="headline"></h3>
                <div class="flex items-center md:ml-3 ml-1" :class="pctUp ? 'text-emerald-700' : 'text-red-600'">
                    <p class="text-xs md:text-base font-semibold" x-text="pctText"></p>
                    <x-lucide-arrow-up x-show="pctUp" class="w-3 h-3" />
                    <x-lucide-arrow-down x-show="!pctUp" class="w-3 h-3" />
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">{{ __('dashboard.chart.vs_prev') }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Toggle dataset: Omzet / Kunjungan --}}
            <div class="flex items-center gap-2">
                <button type="button" @click="setMode('revenue')"
                    :class="mode === 'revenue' ? '' : 'bg-white text-slate-600 border border-slate-200 shadow-sm hover:bg-slate-50 hover:text-slate-900 hover:shadow-md hover:border-slate-300 hover:-translate-y-0.5'"
                    class="py-2 px-4 rounded-2xl ease-in duration-150 text-xs font-semibold focus:outline-none transition-all duration-200"
                    :style="mode === 'revenue' ? 'background: radial-gradient(circle at 20% 20%, #165b38 0%, #0a331f 40%, #051d11 80%); box-shadow: inset 1.5px 2px 3px rgba(255,255,255,0.5), inset -2px -2.5px 4px rgba(0,0,0,0.8), 0 4px 12px rgba(5,29,17,0.5); border: 1px solid rgba(0,0,0,0.5); color: white; transform: translateY(-1px);' : ''">{{ __('dashboard.chart.mode_revenue') }}</button>
                <button type="button" @click="setMode('visits')"
                    :class="mode === 'visits' ? '' : 'bg-white text-slate-600 border border-slate-200 shadow-sm hover:bg-slate-50 hover:text-slate-900 hover:shadow-md hover:border-slate-300 hover:-translate-y-0.5'"
                    class="py-2 px-4 rounded-2xl ease-in duration-150 text-xs font-semibold focus:outline-none transition-all duration-200"
                    :style="mode === 'visits' ? 'background: radial-gradient(circle at 20% 20%, #165b38 0%, #0a331f 40%, #051d11 80%); box-shadow: inset 1.5px 2px 3px rgba(255,255,255,0.5), inset -2px -2.5px 4px rgba(0,0,0,0.8), 0 4px 12px rgba(5,29,17,0.5); border: 1px solid rgba(0,0,0,0.5); color: white; transform: translateY(-1px);' : ''">{{ __('dashboard.chart.mode_visits') }}</button>
            </div>

            {{-- Dropdown periode --}}
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" @keydown.escape.window="open = false"
                    class="inline-flex items-center gap-2 py-2 px-4 text-xs font-semibold text-slate-600 bg-white border border-slate-200 shadow-sm rounded-2xl hover:bg-slate-50 hover:shadow-md hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200">
                    <span x-text="period === 'weekly' ? '{{ __('dashboard.chart.period_weekly') }}' : period === 'monthly' ? '{{ __('dashboard.chart.period_monthly') }}' : '{{ __('dashboard.chart.period_yearly') }}'">{{ __('dashboard.chart.period_monthly') }}</span>
                    <svg class="w-3 h-3 text-slate-500" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    @click.outside="open = false"
                    class="absolute right-0 mt-2 w-36 bg-white rounded-2xl border border-slate-200 shadow-lg py-1.5 z-50">
                    <button type="button" @click="period = 'weekly'; open = false"
                        :class="period === 'weekly' ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                        class="w-full text-left px-4 py-2 text-xs transition-colors duration-100">{{ __('dashboard.chart.period_weekly') }}</button>
                    <button type="button" @click="period = 'monthly'; open = false"
                        :class="period === 'monthly' ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                        class="w-full text-left px-4 py-2 text-xs transition-colors duration-100">{{ __('dashboard.chart.period_monthly') }}</button>
                    <button type="button" @click="period = 'yearly'; open = false"
                        :class="period === 'yearly' ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                        class="w-full text-left px-4 py-2 text-xs transition-colors duration-100">{{ __('dashboard.chart.period_yearly') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Kanvas Chart.js --}}
    <div class="mt-6">
        <div class="relative h-[320px]">
            <canvas x-ref="chartCanvas" role="img" aria-label="{{ __('dashboard.chart.title') }}"></canvas>
        </div>
    </div>
</section>
