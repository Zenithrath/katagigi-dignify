<x-app-layout>
    <x-slot:title>Antrian Hari Ini</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Antrian Hari Ini</h1>
                <p>Check-in appointment → panggil → layani → selesai</p>
            </div>
        </section>

        <x-flash-alerts />

        <div class="content-card overflow-hidden">
            <form method="get" action="{{ route('visits.index') }}" class="p-6 pb-0 flex flex-wrap gap-3 items-end">
                <div class="input-group !mb-0">
                    <label for="date">Tanggal</label>
                    <input type="date" name="date" id="date" class="custom-input" value="{{ request('date', date('Y-m-d')) }}" />
                </div>
                <div class="input-group !mb-0">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="custom-select">
                        <option value="">Antrian aktif</option>
                        @foreach (['REGISTERED', 'WAITING', 'CALLED', 'IN_TREATMENT', 'DONE', 'SIGNED'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-submit !w-auto !px-6">Tampil</button>
            </form>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">No. Visit</th>
                            <th class="py-2 pr-4">Pasien</th>
                            <th class="py-2 pr-4">Dokter</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($visits as $visit)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 font-semibold">{{ $visit->visit_number }}</td>
                                <td class="py-2.5 pr-4">
                                    <span class="font-medium">{{ $visit->patient->name ?? '-' }}</span>
                                    <span class="text-xs text-slate-400 ml-1">{{ $visit->patient->code ?? '' }}</span>
                                </td>
                                <td class="py-2.5 pr-4">{{ $visit->doctor->user->name ?? '-' }}</td>
                                <td class="py-2.5 pr-4">
                                    @php
                                        $badge = match ($visit->clinical_status) {
                                            'WAITING' => 'badge-warning',
                                            'CALLED' => 'badge-info',
                                            'IN_TREATMENT' => 'badge-success',
                                            'DONE' => 'badge-neutral',
                                            'SIGNED' => 'badge-success',
                                            default => 'badge-neutral',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $visit->clinical_status }}</span>
                                </td>
                                <td class="py-2.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('visits.show', $visit->id) }}"
                                            class="text-sm text-brand-600 hover:text-brand-700">Buka</a>
                                        @can('update visit')
                                            @foreach (($transitions[$visit->clinical_status] ?? []) as $next)
                                                <form action="{{ route('visits.status', $visit->id) }}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ $next }}" />
                                                    <button type="submit" class="text-sm text-slate-500 hover:text-emerald-600">
                                                        → {{ $next }}
                                                    </button>
                                                </form>
                                            @endforeach
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-12">
                                <p class="text-sm text-slate-500">Belum ada antrian. Check-in dari detail appointment terkonfirmasi.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($visits->hasPages())
                <div class="p-6 pt-0">{{ $visits->links() }}</div>
            @endif
        </div>
    </main>
</x-app-layout>
