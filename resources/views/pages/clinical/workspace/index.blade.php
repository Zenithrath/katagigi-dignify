<x-app-layout>
    <x-slot:title>Workspace</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Workspace {{ $isDoctor ? 'Dokter' : 'Klinis' }}</h1>
                <p>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }} — layani antrian hari ini</p>
            </div>
        </section>

        <x-flash-alerts />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Menunggu</p>
                <p class="text-2xl font-bold text-amber-600">{{ $waitingCount }}</p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Sedang ditangani</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $treatingCount }}</p>
            </div>
            <div class="content-card !p-5">
                <p class="text-xs text-slate-500">Selesai hari ini</p>
                <p class="text-2xl font-bold text-slate-700">{{ $doneCount }}</p>
            </div>
        </div>

        @if ($current)
            <div class="content-card !p-5 mb-4 !border-emerald-300 !bg-emerald-50">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <p class="text-xs text-emerald-600 font-semibold">PASIEN SAAT INI</p>
                        <p class="text-lg font-bold text-slate-900">{{ $current->patient->name ?? '-' }}
                            <span class="text-xs text-slate-500 font-normal">{{ $current->visit_number }}</span>
                        </p>
                    </div>
                    <a href="{{ route('visits.show', $current->id) }}" class="clickable-primary px-5 py-2.5 rounded-xl">Lanjutkan visit</a>
                </div>
            </div>
        @endif

        <div class="content-card overflow-hidden">
            <div class="p-6 pb-2 flex items-center justify-between">
                <h3 class="font-bold text-slate-900">Antrian {{ $isDoctor ? 'saya' : 'hari ini' }}</h3>
                <a href="{{ route('visits.index') }}" class="text-sm text-brand-600 hover:text-brand-700">Semua antrian →</a>
            </div>
            <div class="p-6 pt-2 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">No. Visit</th>
                            <th class="py-2 pr-4">Pasien</th>
                            @unless ($isDoctor)
                                <th class="py-2 pr-4">Dokter</th>
                            @endunless
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($queue as $visit)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 font-semibold">{{ $visit->visit_number }}</td>
                                <td class="py-2.5 pr-4">{{ $visit->patient->name ?? '-' }}</td>
                                @unless ($isDoctor)
                                    <td class="py-2.5 pr-4">{{ $visit->doctor->user->name ?? '-' }}</td>
                                @endunless
                                <td class="py-2.5 pr-4"><span class="badge badge-warning">{{ $visit->clinical_status }}</span></td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('visits.show', $visit->id) }}" class="text-sm text-brand-600 hover:text-brand-700">Buka</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-12">
                                <p class="text-sm text-slate-500">Antrian kosong. {{ $isDoctor ? 'Pasien berikutnya akan muncul di sini.' : 'Check-in dari appointment terkonfirmasi.' }}</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</x-app-layout>
