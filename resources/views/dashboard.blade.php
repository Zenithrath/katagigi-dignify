<x-app-layout>
    <x-slot:title>Dashboard</x-slot:title>

    <div class="space-y-6">
        {{-- Header Ringkasan & Aksi Cepat --}}
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-[22px] bg-white p-6 sm:p-7 border border-slate-200/90 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 border border-emerald-200">
                        <x-lucide-shield-check class="w-3.5 h-3.5" /> Analisis Bisnis Klinik
                    </span>
                    <span class="text-xs text-slate-400">•</span>
                    <span class="text-xs text-slate-500">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard Manajemen Klinik</h1>
                <p class="mt-1 text-sm text-slate-500">Rangkuman performa finansial, volume pasien, dan analisis kelengkapan data rekam medis.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('patients.create') }}" class="btn-shiny-emerald inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white">
                    <x-lucide-user-plus class="w-4 h-4" /> <span>Pasien Baru</span>
                </a>
                <a href="{{ route('transactions.create') }}" class="btn-shiny-emerald inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white">
                    <x-lucide-receipt class="w-4 h-4" /> Transaksi
                </a>
                <a href="{{ route('export-transactions') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-all">
                    <x-lucide-download class="w-4 h-4" /> Tarik Data
                </a>
            </div>
        </div>

        {{-- 4 Kartu KPI Utama (Background Bersih Tanpa Watermark Icon, Efek card-shiny-emerald 3D di Kartu Hijau) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            {{-- Card 1: Total Pendapatan Bulan Ini (card-shiny-emerald Soft 3D) --}}
            <div class="card-shiny-emerald rounded-[22px] p-6 cursor-default">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-100">Total Pendapatan</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-semibold text-white backdrop-blur-sm border border-white/10">
                        Bulan Ini
                    </span>
                </div>
                <p class="mt-4 text-3xl font-extrabold tracking-tight text-white">{{ $data->revenue ?? 'Rp 0' }}</p>
                <div class="mt-3 flex items-center gap-1.5 text-xs text-emerald-50/90">
                    <x-lucide-check-circle-2 class="w-3.5 h-3.5 text-emerald-200 shrink-0" />
                    <span class="truncate">Dari {{ $data->transactions ?? 0 }} nota pasien selesai</span>
                </div>
            </div>

            {{-- Card 2: Total Pasien Terdaftar --}}
            <div class="rounded-[22px] bg-white p-6 border border-slate-200/90 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-lg hover:border-slate-300 relative overflow-hidden cursor-default">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                        <x-lucide-users class="w-5 h-5" />
                    </div>
                    <span class="inline-flex items-center text-[11px] font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full">
                        Database
                    </span>
                </div>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pasien Terdaftar</p>
                <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($data->patients ?? 0, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs text-slate-500">Keseluruhan pasien terdata di klinik</p>
            </div>

            {{-- Card 3: Pasien Baru Bulan Ini --}}
            <div class="rounded-[22px] bg-white p-6 border border-slate-200/90 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-lg hover:border-slate-300 relative overflow-hidden cursor-default">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                        <x-lucide-user-plus class="w-5 h-5" />
                    </div>
                    <span class="inline-flex items-center text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/70 px-2.5 py-0.5 rounded-full">
                        +{{ $data->new_patients ?? 0 }} baru
                    </span>
                </div>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Pasien Baru Bulan Ini</p>
                <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($data->new_patients ?? 0, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs text-slate-500">Pasien yang baru didata bulan berjalan</p>
            </div>

            {{-- Card 4: Pasien Datang / Kunjungan Selesai --}}
            <div class="rounded-[22px] bg-white p-6 border border-slate-200/90 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-lg hover:border-slate-300 relative overflow-hidden cursor-default">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700">
                        <x-lucide-user-check class="w-5 h-5" />
                    </div>
                    <span class="inline-flex items-center text-[11px] font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full">
                        Bulan Ini
                    </span>
                </div>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Pasien Datang / Kunjungan</p>
                <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">
                    {{ number_format($data->transactions ?? 0, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-400">kunjungan</span>
                </p>
                <p class="mt-2 text-xs text-slate-500">Pasien yang telah selesai tindakan & nota terbit</p>
            </div>
        </div>

        {{-- Section 1: Chart Analisis Pendapatan (Gaya TUK: headline + % badge + toggle + dropdown) --}}
        <div class="rounded-[22px] bg-white p-6 sm:p-7 border border-slate-200/90 shadow-sm transition-all duration-300 hover:shadow-md"
             x-data="revenueChartComponent(@js($data->revenue_analytics ?? []))">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                        <x-lucide-trending-up class="w-5 h-5 text-emerald-600" />
                        Analisis Pendapatan Klinik
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
                    <p class="text-[11px] text-slate-400 mt-1">vs total periode sebelumnya</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Toggle dataset: Omzet / Kunjungan --}}
                    <div class="flex items-center gap-2">
                        <button type="button" @click="setMode('revenue')"
                            :class="mode === 'revenue' ? '' : 'bg-white text-slate-600 border border-slate-200 shadow-sm hover:bg-slate-50 hover:text-slate-900 hover:shadow-md hover:border-slate-300 hover:-translate-y-0.5'"
                            class="py-2 px-4 rounded-2xl ease-in duration-150 text-xs font-semibold focus:outline-none transition-all duration-200"
                            :style="mode === 'revenue' ? 'background: radial-gradient(circle at 20% 20%, #165b38 0%, #0a331f 40%, #051d11 80%); box-shadow: inset 1.5px 2px 3px rgba(255,255,255,0.5), inset -2px -2.5px 4px rgba(0,0,0,0.8), 0 4px 12px rgba(5,29,17,0.5); border: 1px solid rgba(0,0,0,0.5); color: white; transform: translateY(-1px);' : ''">Rupiah</button>
                        <button type="button" @click="setMode('visits')"
                            :class="mode === 'visits' ? '' : 'bg-white text-slate-600 border border-slate-200 shadow-sm hover:bg-slate-50 hover:text-slate-900 hover:shadow-md hover:border-slate-300 hover:-translate-y-0.5'"
                            class="py-2 px-4 rounded-2xl ease-in duration-150 text-xs font-semibold focus:outline-none transition-all duration-200"
                            :style="mode === 'visits' ? 'background: radial-gradient(circle at 20% 20%, #165b38 0%, #0a331f 40%, #051d11 80%); box-shadow: inset 1.5px 2px 3px rgba(255,255,255,0.5), inset -2px -2.5px 4px rgba(0,0,0,0.8), 0 4px 12px rgba(5,29,17,0.5); border: 1px solid rgba(0,0,0,0.5); color: white; transform: translateY(-1px);' : ''">Kunjungan</button>
                    </div>

                    {{-- Dropdown periode --}}
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" @keydown.escape.window="open = false"
                            class="inline-flex items-center gap-2 py-2 px-4 text-xs font-semibold text-slate-600 bg-white border border-slate-200 shadow-sm rounded-2xl hover:bg-slate-50 hover:shadow-md hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200">
                            <span x-text="period === 'weekly' ? 'Mingguan' : period === 'monthly' ? 'Bulanan' : 'Tahunan'">Bulanan</span>
                            <svg class="w-3 h-3 text-slate-500" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            @click.outside="open = false"
                            class="absolute right-0 mt-2 w-36 bg-white rounded-2xl border border-slate-200 shadow-lg py-1.5 z-50">
                            <button type="button" @click="period = 'weekly'; open = false"
                                :class="period === 'weekly' ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                                class="w-full text-left px-4 py-2 text-xs transition-colors duration-100">Mingguan</button>
                            <button type="button" @click="period = 'monthly'; open = false"
                                :class="period === 'monthly' ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                                class="w-full text-left px-4 py-2 text-xs transition-colors duration-100">Bulanan</button>
                            <button type="button" @click="period = 'yearly'; open = false"
                                :class="period === 'yearly' ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                                class="w-full text-left px-4 py-2 text-xs transition-colors duration-100">Tahunan</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kanvas Chart.js --}}
            <div class="mt-6">
                <div class="relative h-[320px]">
                    <canvas x-ref="chartCanvas" role="img" aria-label="Grafik tren pendapatan klinik"></canvas>
                </div>
            </div>
        </div>

        {{-- Section 2: Card Data Pasien Belum Lengkap (Full Width & Terstruktur) --}}
        <div class="rounded-[22px] bg-white p-6 sm:p-7 border border-slate-200/90 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-5 mb-5">
                <div>
                    <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                        <x-lucide-alert-circle class="w-5 h-5 text-amber-500" />
                        Data Pasien Belum Lengkap
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar pasien yang belum melengkapi NIK, No. HP, Tanggal Lahir, atau Alamat untuk rekam medis & integrasi SATUSEHAT.</p>
                </div>
                @if (($data->incomplete_count ?? 0) > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        <x-lucide-alert-triangle class="w-3.5 h-3.5 text-amber-600" /> {{ $data->incomplete_count }} Pasien Perlu Dilengkapi
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <x-lucide-check-circle-2 class="w-3.5 h-3.5 text-emerald-600" /> Semua Data Pasien Lengkap
                    </span>
                @endif
            </div>

            @php
                $incompleteList = $data->incomplete_patients ?? collect();
            @endphp

            @if ($incompleteList->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="pb-3 pl-2">No. RM</th>
                                <th class="pb-3">Nama Pasien</th>
                                <th class="pb-3">Data yang Belum Terisi</th>
                                <th class="pb-3 text-right pr-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach ($incompleteList as $patient)
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
                                            Lengkapi Data <x-lucide-arrow-up-right class="w-3.5 h-3.5" />
                                        </a>
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
                    <p class="text-base font-bold text-slate-800">Semua Data Pasien Sudah Lengkap</p>
                    <p class="text-xs text-slate-500 mt-1 max-w-md">Seluruh pasien di klinik saat ini telah memiliki data NIK, nomor kontak, tanggal lahir, dan alamat lengkap.</p>
                </div>
            @endif

            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan pasien dengan kelengkapan data tertunda</span>
                <a href="{{ route('patients.index') }}" class="font-semibold text-emerald-800 hover:text-emerald-900 inline-flex items-center gap-1">
                    Buka Master Data Pasien <x-lucide-arrow-right class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
