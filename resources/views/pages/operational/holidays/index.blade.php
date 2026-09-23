<x-app-layout>
    <x-slot:title>Tanggal Merah</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Tanggal Merah</h1>
                <p>Hari libur nasional — lembur asisten dihitung sejak jam masuk</p>
            </div>
            <form method="get" action="{{ route('holidays.index') }}" class="flex items-end gap-2">
                <div class="input-group !mb-0">
                    <select name="year" class="custom-select" onchange="this.form.submit()">
                        @for ($y = (int) date('Y') - 1; $y <= (int) date('Y') + 1; $y++)
                            <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </form>
        </section>

        <x-flash-alerts />

        <form method="post" action="{{ route('holidays.store') }}" class="content-card p-6 mb-4">
            @csrf
            <h3 class="font-bold text-slate-900 mb-3">Tambah tanggal merah</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <div class="input-group">
                    <label>Tanggal *</label>
                    <input type="date" name="date" class="custom-input" value="{{ date('Y-m-d') }}" required />
                </div>
                <div class="input-group md:col-span-2">
                    <label>Nama hari libur *</label>
                    <input type="text" name="name" class="custom-input" placeholder="cth. Hari Raya Idul Fitri" required />
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <button type="submit" class="btn-submit !w-auto !px-8">Simpan</button>
            </div>
        </form>

        <div class="content-card overflow-hidden">
            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">Tanggal</th>
                            <th class="py-2 pr-4">Hari</th>
                            <th class="py-2 pr-4">Nama</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($holidays as $h)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 font-semibold">{{ $h->date->format('d M Y') }}</td>
                                <td class="py-2.5 pr-4">{{ $h->date->isoFormat('dddd') }}</td>
                                <td class="py-2.5 pr-4">{{ $h->name }}</td>
                                <td class="py-2.5 text-right">
                                    <form action="{{ route('holidays.destroy', $h->id) }}" method="post"
                                        @submit.prevent="if(confirm('Hapus tanggal merah ini?')) $el.submit()">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-12">
                                <p class="text-sm text-slate-500">Belum ada tanggal merah tahun {{ $year }}.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</x-app-layout>
