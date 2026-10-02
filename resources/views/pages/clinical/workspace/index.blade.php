<x-app-layout>
    <x-slot:title>Workspace</x-slot:title>

    <main class="main-table-container main-table-container--fluid" x-data="{ tab: '{{ $isDoctor ? 'dokter' : 'suster' }}', drawer: null }">
        <section class="heading">
            <div>
                <h1>Workspace {{ $isDoctor ? 'Dokter' : 'Klinis' }}</h1>
                <p>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }} — layani antrian hari ini</p>
            </div>
        </section>

        <x-flash-alerts />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="card-shiny-emerald rounded-[22px] !p-5">
                <p class="text-xs text-sky-200">Menunggu</p>
                <p class="text-2xl font-bold text-white">{{ $waitingCount }}</p>
            </div>
            <div class="card-shiny-emerald rounded-[22px] !p-5">
                <p class="text-xs text-sky-200">Sedang ditangani</p>
                <p class="text-2xl font-bold text-white">{{ $treatingCount }}</p>
            </div>
            <div class="card-shiny-emerald rounded-[22px] !p-5">
                <p class="text-xs text-sky-200">Selesai hari ini</p>
                <p class="text-2xl font-bold text-white">{{ $doneCount }}</p>
            </div>
        </div>

        @if ($current)
            <div class="content-card !p-5 mb-4 !border-emerald-300 !bg-emerald-50">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <p class="text-xs text-emerald-600 font-semibold">PASIEN SAAT INI</p>
                        <p class="text-lg font-bold text-slate-900">{{ $current->patient->name ?? '-' }}
                            <span class="text-xs text-slate-500 font-normal">{{ $current->queueLabel() }} · {{ $current->visit_number }}</span>
                        </p>
                    </div>
                    <a href="{{ route('visits.show', $current->id) }}" wire:navigate class="clickable-primary px-5 py-2.5 rounded-xl">Lanjutkan visit</a>
                </div>
            </div>
        @endif

        <div class="content-card overflow-hidden">
            <div class="flex gap-2 p-6 pb-0">
                <button type="button" x-on:click="tab = 'suster'"
                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
                    :class="tab === 'suster' ? 'clickable-primary' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    Antrean Suster ({{ $nurseQueue->count() }})
                </button>
                <button type="button" x-on:click="tab = 'dokter'"
                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
                    :class="tab === 'dokter' ? 'clickable-primary' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    Antrean Dokter ({{ $doctorQueue->count() }})
                </button>
            </div>

            <div x-show="tab === 'suster'" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-start">
                    <section class="rounded-2xl border border-slate-200/80 bg-white/60 p-4 backdrop-blur min-h-[420px]">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-sm font-bold text-amber-700">Antrean Triase</h4>
                            <span class="badge badge-warning">{{ $nurseQueue->count() }} Pasien</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">Klik kartu untuk isi form.</p>
                        <div class="space-y-3">
                            @forelse ($nurseQueue as $visit)
                                @include('pages.clinical.workspace.partials.board-card', ['visit' => $visit, 'transitions' => $transitions, 'hideDoctor' => $isDoctor])
                            @empty
                                <div class="text-center py-10 text-slate-400">
                                    <p class="text-sm font-semibold text-slate-500">Antrean kosong.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200/80 bg-white/60 p-4 backdrop-blur min-h-[420px]">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-sm font-bold text-sky-700">Antrean Masuk Dokter</h4>
                            <span class="badge badge-info">{{ $doctorQueue->where('clinical_status', 'CALLED')->count() }} Pasien</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">Menunggu dipanggil dokter.</p>
                        <div class="space-y-3">
                            @forelse ($doctorQueue->where('clinical_status', 'CALLED')->values() as $visit)
                                @include('pages.clinical.workspace.partials.board-card', ['visit' => $visit, 'transitions' => $transitions, 'hideDoctor' => $isDoctor])
                            @empty
                                <div class="text-center py-10 text-slate-400">
                                    <p class="text-sm font-semibold text-slate-500">Antrean kosong.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200/80 bg-white/60 p-4 backdrop-blur min-h-[420px]">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-sm font-bold text-slate-700">Diperiksa &amp; Selesai</h4>
                            <span class="badge badge-success">{{ $doctorQueue->where('clinical_status', 'IN_TREATMENT')->count() + $doneToday->count() }} Pasien</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">Sedang ditangani paling atas.</p>
                        <div class="space-y-3">
                            @forelse ($doctorQueue->where('clinical_status', 'IN_TREATMENT')->values()->concat($doneToday)->values() as $visit)
                                @include('pages.clinical.workspace.partials.board-card', ['visit' => $visit, 'transitions' => $transitions, 'hideDoctor' => $isDoctor, 'showBilling' => true])
                            @empty
                                <div class="text-center py-10 text-slate-400">
                                    <svg class="w-10 h-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" /></svg>
                                    <p class="text-sm font-semibold text-slate-500">Tidak ada pemeriksaan aktif!</p>
                                    <p class="text-xs mt-1">Belum ada pasien pada tahap ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>

            <div x-show="tab === 'dokter'" class="p-6">
                <p class="text-xs text-slate-500 mb-3">Menunggu dipanggil + sedang diperiksa. Klik baris untuk masuk form.{{ $isDoctor ? ' Daftar ini hanya pasien Anda.' : '' }}</p>
                @include('pages.clinical.workspace.partials.queue-table', ['rows' => $doctorQueue, 'transitions' => $transitions, 'showDoctor' => ! $isDoctor, 'clickableRow' => $isDoctor])
            </div>
        </div>

        {{-- Drawer triase di level halaman: bisa dibuka dari kartu/tab mana pun. --}}
        @foreach ($nurseQueue->concat($doctorQueue)->concat($doneToday)->unique('id')->values() as $visit)
            @include('pages.clinical.workspace.partials.triage-drawer', ['visit' => $visit])
        @endforeach
    </main>

@pushOnce('scripts')
    <script type="text/javascript">
        (function () {
            function pad(n) { return String(n).padStart(2, '0'); }
            function tick() {
                var now = Date.now();
                document.querySelectorAll('.wait-timer[data-since]').forEach(function (el) {
                    var s = Math.max(0, Math.floor((now - new Date(el.getAttribute('data-since')).getTime()) / 1000));
                    var h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), sec = s % 60;
                    el.textContent = (h > 0 ? h + ':' + pad(m) : pad(m)) + ':' + pad(sec);
                });
            }
            document.addEventListener('DOMContentLoaded', function () { tick(); setInterval(tick, 1000); });
        })();
    </script>
@endPushOnce
</x-app-layout>
