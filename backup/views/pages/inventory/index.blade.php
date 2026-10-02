<x-app-layout>
    <x-slot:title>Inventory</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Inventory</h1>
                <p>Stok bahan & obat habis pakai — FIFO per batch</p>
            </div>
            @can('manage inventory')
                <a href="{{ route('inventory.create') }}" class="clickable-primary px-5 py-2.5 rounded-xl">+ Item</a>
            @endcan
        </section>

        <x-flash-alerts />

        @if ($lowCount > 0)
            <div class="content-card !p-4 mb-4 !bg-amber-50 !border-amber-200">
                <p class="text-sm font-semibold text-amber-700">{{ $lowCount }} item di bawah stok minimum — segera pesan ulang.</p>
            </div>
        @endif

        <div class="content-card overflow-hidden">
            <form method="get" action="{{ route('inventory.index') }}" class="p-6 pb-0 flex flex-wrap gap-3 items-end">
                <div class="input-group !mb-0">
                    <label for="q">Cari</label>
                    <input type="text" name="q" id="q" class="custom-input" placeholder="nama / kode" value="{{ request('q') }}" />
                </div>
                <button type="submit" class="btn-submit !w-auto !px-6">Tampil</button>
            </form>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">Kode</th>
                            <th class="py-2 pr-4">Nama</th>
                            <th class="py-2 pr-4">Stok</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 font-semibold">{{ $item->code }}</td>
                                <td class="py-2.5 pr-4">{{ $item->name }}</td>
                                <td class="py-2.5 pr-4 font-semibold">{{ rtrim(rtrim(number_format($item->currentStock(), 2), '0'), '.') }} {{ $item->unit }}</td>
                                <td class="py-2.5 pr-4">
                                    @if ($item->isLowStock())
                                        <span class="badge badge-danger">MENIPIS</span>
                                    @else
                                        <span class="badge badge-success">AMAN</span>
                                    @endif
                                </td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('inventory.show', $item->id) }}" class="text-sm text-brand-600 hover:text-brand-700">Buka</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-12">
                                <p class="text-sm text-slate-500">Belum ada item.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="p-6 pt-0">{{ $items->links() }}</div>
            @endif
        </div>
    </main>
</x-app-layout>
