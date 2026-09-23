<div class="w-full">
    {{-- Kode terpilih (chip) + hidden input untuk form induk --}}
    @if (count($selected) > 0)
        <div class="flex flex-wrap gap-2 mb-2">
            @foreach ($selected as $index => $row)
                <span class="inline-flex items-center gap-2 rounded-md bg-emerald-50 border border-emerald-200 pl-3 pr-1.5 py-1 text-sm text-emerald-900">
                    <span class="text-[11px] font-semibold uppercase tracking-wide bg-emerald-700 text-white rounded px-2 py-0.5">{{ $row['system'] }}</span>
                    <span class="font-semibold">[{{ $row['code'] }}]</span>
                    <span class="text-emerald-800">{{ $row['display_id'] }}</span>
                    <button type="button" wire:click="remove({{ $index }})" wire:loading.attr="disabled" class="rounded hover:bg-emerald-200 w-6 h-6 leading-none disabled:opacity-50" aria-label="Hapus kode">&times;</button>
                </span>
                <input type="hidden" name="{{ $fieldName }}[]" value="{{ $row['id'] }}">
            @endforeach
        </div>
    @endif

    <div class="relative">
        <input
            type="text"
            wire:model.live.debounce.250ms="query"
            placeholder="Ketik keluhan awam, mis. gigi berlubang / cabut gigi…"
            autocomplete="off"
            class="w-full rounded-md border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 text-sm py-2.5"
        >

        @if (strlen(trim($query)) >= 2)
            <div wire:loading.class="opacity-50 pointer-events-none" class="absolute z-20 mt-1 w-full overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg">
                <p wire:loading wire:target="query" class="px-3 py-2 text-xs text-slate-400">Mencari…</p>
                @forelse ($results as $item)
                    <button
                        type="button"
                        wire:click="select('{{ $item->id }}')"
                        wire:loading.attr="disabled"
                        class="flex w-full items-start gap-2 px-3 py-2.5 text-left hover:bg-emerald-50 disabled:opacity-50"
                    >
                        <span class="mt-0.5 shrink-0 text-[11px] font-semibold uppercase tracking-wide rounded px-2 py-0.5
                            {{ $item->system === 'ICD10' ? 'bg-emerald-700 text-white' : ($item->system === 'ICD9' ? 'bg-teal-600 text-white' : 'bg-lime-600 text-white') }}">
                            {{ $item->system }}
                        </span>
                        <span class="text-sm">
                            <span class="font-semibold text-slate-900">[{{ $item->code }}]</span>
                            <span class="text-slate-600">{{ $item->display_id }}</span>
                        </span>
                    </button>
                @empty
                    <p class="px-3 py-3 text-sm text-slate-500">Tidak ketemu. Coba kata lain, atau tulis di catatan bebas.</p>
                @endforelse
            </div>
        @endif
    </div>

    @if (count($selected) === 0)
        <p class="mt-1.5 text-xs text-slate-500">Wajib pilih minimal 1 kode resmi. Nakes tidak perlu hafal kode.</p>
    @endif
</div>
