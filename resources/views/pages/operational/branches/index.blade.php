<x-app-layout>
    <x-slot:title>Cabang</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Cabang</h1>
                <p>Kelola cabang klinik (multi-branch Fase 4)</p>
            </div>
        </section>

        <x-flash-alerts />

        <form method="post" action="{{ route('branches.store') }}" class="content-card p-6 mb-4">
            @csrf
            <h3 class="font-bold text-slate-900 mb-3">Tambah cabang</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                <div class="input-group">
                    <label>Kode *</label>
                    <input type="text" name="code" class="custom-input" placeholder="CBG-02" required />
                </div>
                <div class="input-group">
                    <label>Nama *</label>
                    <input type="text" name="name" class="custom-input" required />
                </div>
                <div class="input-group">
                    <label>Telepon</label>
                    <input type="text" name="phone" class="custom-input" />
                </div>
                <div class="input-group">
                    <label>Alamat</label>
                    <input type="text" name="address" class="custom-input" />
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
                            <th class="py-2 pr-4">Kode</th>
                            <th class="py-2 pr-4">Nama</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($branches as $branch)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 font-semibold">{{ $branch->code }}</td>
                                <td class="py-2.5 pr-4">{{ $branch->name }}</td>
                                <td class="py-2.5 pr-4">
                                    <span class="badge {{ $branch->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $branch->is_active ? 'AKTIF' : 'NONAKTIF' }}</span>
                                </td>
                                <td class="py-2.5 text-right">
                                    <form action="{{ route('branches.toggle', $branch->id) }}" method="post">
                                        @csrf
                                        <button type="submit" class="text-sm text-slate-500 hover:text-emerald-600">{{ $branch->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</x-app-layout>
