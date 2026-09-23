<x-app-layout>
    <x-slot:title>SATUSEHAT</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>SATUSEHAT <span class="text-xs font-normal text-slate-400">({{ $env }})</span></h1>
                <p>Monitoring bridging — sandbox dulu, tanpa klaim produksi</p>
            </div>
            <span class="badge {{ $enabled ? 'badge-success' : 'badge-neutral' }}">{{ $enabled ? 'TERKONFIGURASI' : 'BELUM DIKONFIGURASI' }}</span>
        </section>

        <x-flash-alerts />

        @unless ($enabled)
            <div class="content-card !p-4 mb-4 !bg-amber-50 !border-amber-200">
                <p class="text-sm text-amber-700">Isi <code>SATUSEHAT_CLIENT_ID</code> / <code>SATUSEHAT_CLIENT_SECRET</code> / <code>SATUSEHAT_ORG_ID</code> di <code>.env</code> lalu uji ke sandbox. Sinkron hanya untuk visit SIGNED.</p>
            </div>
        @endunless

        <div class="content-card overflow-hidden">
            <form method="get" action="{{ route('satusehat.index') }}" class="p-6 pb-0 flex flex-wrap gap-3 items-end">
                <div class="input-group !mb-0">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="custom-select">
                        <option value="">Semua</option>
                        @foreach (['PENDING', 'SUCCESS', 'FAILED', 'SKIPPED'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group !mb-0">
                    <label for="resource">Resource</label>
                    <select name="resource" id="resource" class="custom-select">
                        <option value="">Semua</option>
                        @foreach (['Patient', 'Encounter', 'Condition', 'Procedure'] as $r)
                            <option value="{{ $r }}" @selected(request('resource') === $r)>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-submit !w-auto !px-6">Tampil</button>
            </form>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">Waktu</th>
                            <th class="py-2 pr-4">Visit</th>
                            <th class="py-2 pr-4">Resource</th>
                            <th class="py-2 pr-4">External ID</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 text-xs">{{ $log->created_at?->format('d M Y H:i') }}</td>
                                <td class="py-2.5 pr-4">
                                    @if ($log->visit)
                                        <a href="{{ route('visits.show', $log->visit_id) }}" class="text-brand-600 hover:text-brand-700">{{ $log->visit->visit_number }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-2.5 pr-4">{{ $log->resource_type }}</td>
                                <td class="py-2.5 pr-4 font-mono text-xs">{{ $log->external_id ?? '-' }}</td>
                                <td class="py-2.5 pr-4">
                                    <span class="badge {{ $log->status === 'SUCCESS' ? 'badge-success' : ($log->status === 'FAILED' ? 'badge-danger' : ($log->status === 'SKIPPED' ? 'badge-neutral' : 'badge-warning')) }}">{{ $log->status }}</span>
                                </td>
                                <td class="py-2.5 pr-4 text-xs text-slate-500">{{ $log->error ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-12">
                                <p class="text-sm text-slate-500">Belum ada sinkronisasi.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="p-6 pt-0">{{ $logs->links() }}</div>
            @endif
        </div>
    </main>
</x-app-layout>
