<x-app-layout>
    <div class="space-y-3 sm:space-y-4">
        {{-- Header ala Donezo --}}
        <div class="flex flex-wrap items-start gap-3 rounded-3xl bg-white p-5 sm:p-6 shadow-sm border border-white">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-extrabold tracking-tight">Dashboard</h1>
                <p class="mt-0.5 text-sm text-slate-400">Rencanakan, prioritaskan, dan layani pasien dengan mudah.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('appointments.create') }}" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">＋ Reservasi</a>
                <a href="{{ route('export-transactions') }}" class="inline-flex items-center rounded-full border border-emerald-800 px-4 py-2 text-sm font-semibold text-emerald-900 hover:bg-emerald-50">Tarik Data</a>
            </div>
        </div>

        {{-- 4 kartu statistik (data live dari DashboardController) --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
            <div class="rounded-3xl bg-emerald-800 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-medium text-emerald-100">Antrian Minggu Ini</p>
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-white text-emerald-800 text-sm">↗</span>
                </div>
                <p class="mt-2 text-4xl font-extrabold">{{ isset($data->appointments) ? count($data->appointments) : 0 }}</p>
                <p class="mt-1 text-xs text-emerald-200">Belum dilayani & belum batal</p>
            </div>
            <div class="rounded-3xl bg-white p-5 shadow-sm border border-white">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-medium text-slate-500">Pasien Terdaftar</p>
                    <span class="grid h-7 w-7 place-items-center rounded-full border border-slate-200 text-sm">↗</span>
                </div>
                <p class="mt-2 text-4xl font-extrabold">{{ $data->patients ?? ($data->medical_records ?? 0) }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ isset($data->patients) ? 'Total pasien klinik' : 'Rekam medis bulan ini' }}</p>
            </div>
            <div class="rounded-3xl bg-white p-5 shadow-sm border border-white">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-medium text-slate-500">Transaksi Bulan Ini</p>
                    <span class="grid h-7 w-7 place-items-center rounded-full border border-slate-200 text-sm">↗</span>
                </div>
                <p class="mt-2 text-4xl font-extrabold">{{ $data->transactions ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-400">Nota terbit bulan berjalan</p>
            </div>
            <div class="rounded-3xl bg-white p-5 shadow-sm border border-white">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-medium text-slate-500">Omzet Bulan Ini</p>
                    <span class="grid h-7 w-7 place-items-center rounded-full border border-slate-200 text-sm">↗</span>
                </div>
                <p class="mt-2 text-2xl font-extrabold">{{ $data->revenue ?? 'Rp 0' }}</p>
                <p class="mt-1 text-xs text-slate-400">Pendapatan bulan berjalan</p>
            </div>
        </div>

        {{-- Analitik + pengingat + antrian --}}
        <div class="grid lg:grid-cols-3 gap-3 sm:gap-4">
            <div class="rounded-3xl bg-white p-5 shadow-sm border border-white">
                <h3 class="font-bold">Analitik Pendapatan</h3>
                <div class="mt-4 flex h-36 items-end justify-between gap-2">
                    @foreach (['S','S','R','K','J','S','M'] as $i => $d)
                        <div class="flex flex-1 flex-col items-center gap-1.5">
                            <div class="w-full rounded-full {{ $i % 3 === 0 ? 'bg-emerald-700' : ($i % 3 === 1 ? 'bg-emerald-300' : 'bg-slate-200') }}" style="height: {{ [38, 62, 48, 84, 56, 30, 44][$i] }}%"></div>
                            <span class="text-[11px] text-slate-400">{{ $d }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-slate-400">Contoh visual — grafik live aktif setelah modul laporan.</p>
            </div>
            <div class="rounded-3xl bg-white p-5 shadow-sm border border-white">
                <h3 class="font-bold">Pengingat</h3>
                <p class="mt-2 font-semibold leading-snug">Belum ada kontrol berikutnya</p>
                <p class="text-xs text-slate-400">Jadwal kontrol & cicilan jatuh tempo muncul di sini.</p>
                <span class="mt-4 flex cursor-not-allowed items-center justify-center gap-2 rounded-full bg-emerald-700 py-2.5 text-sm font-semibold text-white/90">✆ Ingatkan via WA</span>
            </div>
            <div class="rounded-3xl bg-white p-5 shadow-sm border border-white">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold">Antrian</h3>
                    <span class="rounded-full border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-500">＋ Baru</span>
                </div>
                <p class="mt-3 text-sm text-slate-400">Belum ada antrian hari ini.</p>
            </div>
        </div>

        {{-- Jadwal + progres + kasir --}}
        <div class="grid lg:grid-cols-3 gap-3 sm:gap-4">
            <div class="rounded-3xl bg-white p-5 shadow-sm border border-white">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold">Jadwal Dokter</h3>
                    <span class="rounded-full border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-500">＋ Tambah</span>
                </div>
                <p class="mt-3 text-sm text-slate-400">Belum ada jadwal. Modul penjadwalan menyusul.</p>
            </div>
            <div class="rounded-3xl bg-white p-5 shadow-sm border border-white">
                <h3 class="font-bold">Keterisian Jadwal</h3>
                <div class="mx-auto mt-2 grid h-36 w-36 place-items-center rounded-full" style="background: conic-gradient(#047857 0%, #047857 0%, #e2e8f0 0%);">
                    <div class="grid h-24 w-24 place-items-center rounded-full bg-white text-center">
                        <div><p class="text-2xl font-extrabold">0%</p><p class="text-[10px] text-slate-400">Terisi</p></div>
                    </div>
                </div>
                <div class="mt-3 flex justify-center gap-4 text-[11px] text-slate-500">
                    <span><i class="mr-1 inline-block h-2 w-2 rounded-full bg-emerald-700"></i>Terisi</span>
                    <span><i class="mr-1 inline-block h-2 w-2 rounded-full bg-emerald-950"></i>Selesai</span>
                    <span><i class="mr-1 inline-block h-2 w-2 rounded-full bg-slate-300"></i>Kosong</span>
                </div>
            </div>
            <div class="rounded-3xl bg-emerald-950 p-5 text-white shadow-sm">
                <h3 class="font-bold">Kasir Hari Ini</h3>
                <p class="mt-4 text-4xl font-extrabold tracking-tight">Rp 0</p>
                <p class="mt-1 text-xs text-emerald-200/70">0 nota • modul kasir menyusul</p>
                <div class="mt-5 flex justify-center gap-3">
                    <span class="grid h-11 w-11 cursor-not-allowed place-items-center rounded-full bg-white text-emerald-950">⏸</span>
                    <span class="grid h-11 w-11 cursor-not-allowed place-items-center rounded-full bg-red-500 text-white">⏹</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
