<x-app-layout>
    <x-slot:title>{{ $item->name }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <x-back-button href="{{ route('inventory.index') }}" />
                <h1>{{ $item->name }}</h1>
                <p>{{ $item->code }} — stok {{ rtrim(rtrim(number_format($item->currentStock(), 2), '0'), '.') }} {{ $item->unit }}</p>
            </div>
            @if ($item->isLowStock())
                <span class="badge badge-danger">MENIPIS (min. {{ $item->min_stock }})</span>
            @endif
        </section>

        <x-flash-alerts />

        <div class="content-card p-8 mb-4">
            <h3 class="font-bold text-slate-900 mb-2">Batch ({{ $item->batches->count() }})</h3>
            @forelse ($item->batches as $batch)
                <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-100 last:border-0 text-sm flex-wrap">
                    <span>
                        <span class="font-semibold">{{ $batch->batch_no }}</span>
                        <span class="text-slate-500">· {{ $batch->quantity }} {{ $item->unit }}</span>
                        @if ($batch->expiry_date)
                            <span class="ml-1 text-xs px-2 py-0.5 rounded-md {{ $batch->isExpired() ? 'bg-red-100 text-red-700' : ($batch->expiresSoon() ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                ED {{ $batch->expiry_date->format('M Y') }}
                            </span>
                        @endif
                    </span>
                    @can('manage inventory')
                        <form action="{{ route('inventory.adjust', [$item->id, $batch->id]) }}" method="post" class="flex items-center gap-1">
                            @csrf
                            <input type="number" name="quantity" class="custom-input !py-1 !px-2 !text-xs !w-24" min="0" step="0.01" value="{{ $batch->quantity }}" title="Hasil opname" />
                            <button type="submit" class="text-xs text-slate-500 hover:text-emerald-600">Opname</button>
                        </form>
                    @endcan
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada batch. Catat penerimaan pertama di bawah.</p>
            @endforelse

            @can('manage inventory')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 pt-6 border-t border-slate-200">
                    <form method="post" action="{{ route('inventory.receive', $item->id) }}">
                        @csrf
                        <h4 class="text-sm font-bold text-slate-700 mb-2">Terima stok</h4>
                        <div class="input-group">
                            <label>No. batch *</label>
                            <input type="text" name="batch_no" class="custom-input" required />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="input-group">
                                <label>Jumlah *</label>
                                <input type="number" name="quantity" class="custom-input" min="1" step="0.01" required />
                            </div>
                            <div class="input-group">
                                <label>Harga beli</label>
                                <input type="number" name="buy_price" class="custom-input" min="0" value="0" />
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Kedaluwarsa</label>
                            <input type="date" name="expiry_date" class="custom-input" />
                        </div>
                        <button type="submit" class="btn-submit !w-auto !px-6 mt-1">Simpan masuk</button>
                    </form>
                    <form method="post" action="{{ route('inventory.dispense', $item->id) }}">
                        @csrf
                        <h4 class="text-sm font-bold text-slate-700 mb-2">Keluarkan stok (FIFO)</h4>
                        <div class="input-group">
                            <label>Jumlah *</label>
                            <input type="number" name="quantity" class="custom-input" min="1" step="0.01" required />
                        </div>
                        <div class="input-group">
                            <label>Referensi (mis. no. resep)</label>
                            <input type="text" name="reference" class="custom-input" />
                        </div>
                        <button type="submit" class="btn-submit !w-auto !px-6 mt-1">Simpan keluar</button>
                    </form>
                </div>
            @endcan
        </div>

        <div class="content-card p-8">
            <h3 class="font-bold text-slate-900 mb-2">Riwayat movement</h3>
            <ul class="divide-y divide-slate-100">
                @forelse ($item->movements->take(30) as $move)
                    <li class="py-2 flex items-center justify-between gap-3 text-sm">
                        <span>
                            <span class="text-xs px-2 py-0.5 rounded-md mr-1 {{ $move->type === 'IN' ? 'bg-emerald-100 text-emerald-700' : ($move->type === 'OUT' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $move->type }}</span>
                            {{ $move->quantity }} {{ $item->unit }}
                            @if ($move->batch)<span class="text-slate-400">· {{ $move->batch->batch_no }}</span>@endif
                            @if ($move->reference)<span class="text-slate-500">· {{ $move->reference }}</span>@endif
                        </span>
                        <span class="text-xs text-slate-400">{{ $move->created_at?->format('d M Y H:i') }}</span>
                    </li>
                @empty
                    <p class="text-sm text-slate-500">Belum ada movement.</p>
                @endforelse
            </ul>
        </div>
    </main>
</x-app-layout>
