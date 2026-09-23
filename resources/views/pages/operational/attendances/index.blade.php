<x-app-layout>
    <x-slot:title>Jam Kerja Asisten</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Jam Kerja Asisten</h1>
                <p>Jam masuk & pulang harian — dasar hitung lembur Rp{{ number_format(config('clinic.overtime_rate'), 0, ',', '.') }}/jam</p>
            </div>
        </section>

        <x-flash-alerts />

        <form method="post" action="{{ route('attendances.store') }}" class="content-card p-6 mb-4">
            @csrf
            <h3 class="font-bold text-slate-900 mb-3">Catat jam kerja</h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-2">
                @if ($canManage)
                    <div class="input-group">
                        <label>Asisten *</label>
                        <select name="user_id" class="custom-select">
                            @foreach ($nurses as $n)
                                <option value="{{ $n->user_id }}">{{ $n->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="input-group">
                    <label>Tanggal *</label>
                    <input type="date" name="date" class="custom-input" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required />
                </div>
                <div class="input-group">
                    <label>Masuk *</label>
                    <input type="time" name="clock_in" class="custom-input" required />
                </div>
                <div class="input-group">
                    <label>Pulang *</label>
                    <input type="time" name="clock_out" class="custom-input" required />
                </div>
                <div class="input-group">
                    <label>Seharusnya pulang *</label>
                    <input type="time" name="scheduled_end" class="custom-input" value="{{ $defaultEnd }}" required />
                </div>
                <div class="input-group">
                    <label>Catatan</label>
                    <input type="text" name="note" class="custom-input" placeholder="opsional" />
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <button type="submit" class="btn-submit !w-auto !px-8">Simpan</button>
            </div>
        </form>

        <div class="content-card overflow-hidden">
            <form method="get" action="{{ route('attendances.index') }}" class="p-6 pb-0 flex flex-wrap gap-3 items-end">
                <div class="input-group !mb-0">
                    <label for="month">Bulan</label>
                    <input type="month" name="month" id="month" class="custom-input" value="{{ $month }}" onchange="this.form.submit()" />
                </div>
                @if ($canManage)
                    <div class="input-group !mb-0">
                        <label for="nurse_id">Asisten</label>
                        <select name="nurse_id" id="nurse_id" class="custom-select" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @foreach ($nurses as $n)
                                <option value="{{ $n->user_id }}" @selected($nurse_id === $n->user_id)>{{ $n->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </form>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">Tanggal</th>
                            <th class="py-2 pr-4">Asisten</th>
                            <th class="py-2 pr-4">Masuk</th>
                            <th class="py-2 pr-4">Pulang</th>
                            <th class="py-2 pr-4">Seharusnya</th>
                            <th class="py-2 pr-4">Catatan</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $r)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 font-semibold">{{ $r->date->format('d M Y') }}</td>
                                <td class="py-2.5 pr-4">{{ $r->user->name ?? '-' }}</td>
                                <td class="py-2.5 pr-4">{{ substr($r->clock_in, 0, 5) }}</td>
                                <td class="py-2.5 pr-4">{{ substr($r->clock_out, 0, 5) }}</td>
                                <td class="py-2.5 pr-4 text-slate-500">{{ substr($r->scheduled_end, 0, 5) }}</td>
                                <td class="py-2.5 pr-4 text-slate-500">{{ $r->note ?? '-' }}</td>
                                <td class="py-2.5 text-right">
                                    <form action="{{ route('attendances.destroy', $r->id) }}" method="post" class="inline"
                                        @submit.prevent="if(confirm('Hapus catatan ini?')) $el.submit()">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-12">
                                <p class="text-sm text-slate-500">Belum ada catatan jam kerja.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rows->hasPages())
                <div class="p-6 pt-0">{{ $rows->links() }}</div>
            @endif
        </div>
    </main>
</x-app-layout>
