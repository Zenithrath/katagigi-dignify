<x-app-layout>
    <x-slot:title>{{ $type == 'update' ? 'Ubah Item' : 'Item Baru' }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ $type == 'update' ? 'Ubah Item' : 'Item Inventory Baru' }}</h1>
                <p>Master bahan & obat habis pakai</p>
            </div>
        </section>

        <x-flash-alerts />

        <form method="post" action="{{ $action }}">
            @csrf
            <div class="content-card p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                    <div class="input-group">
                        <label for="code">Kode <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" class="custom-input" placeholder="mis. KOM-001" value="{{ $data->code ?? '' }}" required />
                        @error('code')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="name">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" class="custom-input" placeholder="mis. Komposit A2" value="{{ $data->name ?? '' }}" required />
                        @error('name')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="unit">Satuan</label>
                        <input type="text" name="unit" id="unit" class="custom-input" placeholder="pcs" value="{{ $data->unit ?? 'pcs' }}" />
                    </div>
                    <div class="input-group">
                        <label for="min_stock">Stok minimum (peringatan)</label>
                        <input type="number" name="min_stock" id="min_stock" class="custom-input" min="0" value="{{ $data->min_stock ?? 0 }}" />
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="btn-submit !w-auto !px-8">Simpan</button>
                </div>
            </div>
        </form>
    </main>
</x-app-layout>
