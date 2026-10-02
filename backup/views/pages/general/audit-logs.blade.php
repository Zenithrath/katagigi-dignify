<x-app-layout>
    <x-slot:title>Audit Log</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Audit Log</h1>
                <p>Jejak aktivitas sistem: siapa melakukan apa, kapan, dan mengapa (Permenkes 24/2022).</p>
            </div>
        </section>

        <div class="content-card">
            <form action="{{ route('audit-logs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1.5 block">Aksi</label>
                    <select name="action" class="custom-select">
                        <option value="">Semua aksi</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1.5 block">Pengguna</label>
                    <select name="user" class="custom-select">
                        <option value="">Semua pengguna</option>
                        @foreach ($users as $id => $name)
                            <option value="{{ $id }}" @selected(request('user') === $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1.5 block">Tanggal</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="custom-input" />
                </div>
                <div class="flex gap-2">
                    <button class="clickable-primary py-2.5 px-5 rounded-xl text-sm font-bold" type="submit">Filter</button>
                    <a href="{{ route('audit-logs.index') }}" class="py-2.5 px-4 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-100">Reset</a>
                </div>
            </form>
        </div>

        <section class="table-content">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="column">Waktu</th>
                        <th scope="col" class="column">Pengguna</th>
                        <th scope="col" class="column">Aksi</th>
                        <th scope="col" class="column">Objek</th>
                        <th scope="col" class="column">Alasan</th>
                        <th scope="col" class="column">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="column whitespace-nowrap">
                                <span class="font-semibold text-slate-900">{{ \Carbon\Carbon::parse($log->created_at)->locale(app()->getLocale())->setTimezone('Asia/Jakarta')->isoFormat('DD MMM YY HH:mm') }}</span>
                            </td>
                            <td class="column">
                                <span class="font-medium">{{ $log->user->name ?? 'Sistem' }}</span>
                            </td>
                            <td class="column">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700">{{ $log->action }}</span>
                            </td>
                            <td class="column">
                                <span class="text-xs text-slate-500">{{ $log->entity_type }}</span>
                                <span class="block font-mono text-xs text-slate-400">{{ $log->entity_id ? substr($log->entity_id, 0, 8) : '—' }}</span>
                            </td>
                            <td class="column max-w-xs">
                                <span class="text-sm">{{ $log->reason ?? '—' }}</span>
                            </td>
                            <td class="column">
                                <span class="font-mono text-xs text-slate-400">{{ $log->ip_address ?? '—' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="column text-center" colspan="6">
                                <div class="h-24 w-full flex items-center justify-center text-slate-400">Belum ada aktivitas tercatat.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <x-table-paginator :paginator="$logs" />
    </main>
</x-app-layout>
