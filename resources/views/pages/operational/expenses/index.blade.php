<x-app-layout>
    <x-slot:title>Beban Operasional</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Beban Operasional</h1>
                <p>Pengeluaran klinik di luar jasa medis</p>
            </div>
        </section>

        <x-flash-alerts />

        @can('manage expense')
            <form method="post" action="{{ route('expenses.store') }}" class="content-card p-6 mb-4">
                @csrf
                <h3 class="font-bold text-slate-900 mb-3">Catat beban</h3>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                    <div class="input-group">
                        <label>Kategori *</label>
                        <select name="category" class="custom-select">
                            @foreach (\App\Models\Expense::CATEGORIES as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Nominal (Rp) *</label>
                        <input type="number" name="amount" class="custom-input" min="1" required />
                    </div>
                    <div class="input-group">
                        <label>Tanggal *</label>
                        <input type="date" name="spent_at" class="custom-input" value="{{ date('Y-m-d') }}" required />
                    </div>
                    <div class="input-group md:col-span-2">
                        <label>Keterangan</label>
                        <input type="text" name="description" class="custom-input" />
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="btn-submit !w-auto !px-8">Simpan</button>
                </div>
            </form>
        @endcan

        <div class="content-card overflow-hidden">
            <form method="get" action="{{ route('expenses.index') }}" class="p-6 pb-0 flex flex-wrap gap-3 items-end">
                <div class="input-group !mb-0">
                    <label for="month">Bulan</label>
                    <input type="month" name="month" id="month" class="custom-input" value="{{ request('month') }}" />
                </div>
                <div class="input-group !mb-0">
                    <label for="category">Kategori</label>
                    <select name="category" id="category" class="custom-select">
                        <option value="">Semua</option>
                        @foreach (\App\Models\Expense::CATEGORIES as $c)
                            <option value="{{ $c }}" @selected(request('category') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-submit !w-auto !px-6">Tampil</button>
                <span class="text-sm font-bold text-slate-700 ml-auto">Total tampil: Rp{{ number_format($monthTotal, 0, ',', '.') }}</span>
            </form>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">Tanggal</th>
                            <th class="py-2 pr-4">Kategori</th>
                            <th class="py-2 pr-4">Keterangan</th>
                            <th class="py-2 pr-4">Nominal</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4">{{ $expense->spent_at?->format('d M Y') }}</td>
                                <td class="py-2.5 pr-4"><span class="badge badge-neutral">{{ $expense->category }}</span></td>
                                <td class="py-2.5 pr-4">{{ $expense->description ?? '-' }}</td>
                                <td class="py-2.5 pr-4 font-semibold">Rp{{ number_format($expense->amount, 0, ',', '.') }}</td>
                                <td class="py-2.5 text-right">
                                    @can('manage expense')
                                        <form action="{{ route('expenses.destroy', $expense->id) }}" method="post"
                                            @submit.prevent="if(confirm('Hapus beban ini?')) $el.submit()">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-12">
                                <p class="text-sm text-slate-500">Belum ada beban.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($expenses->hasPages())
                <div class="p-6 pt-0">{{ $expenses->links() }}</div>
            @endif
        </div>
    </main>
</x-app-layout>
