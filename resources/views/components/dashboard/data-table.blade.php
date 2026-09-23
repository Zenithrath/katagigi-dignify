{{--
    Tabel data dashboard: satu-satunya pola tabel (thead uppercase tipis,
    baris hover, empty state seragam). `<tr>` custom dikirim via slot rows;
    `<th>` custom via slot head agar tiap widget bebas menentukan kolom.
--}}

@props([
    'empty' => false,          // bool: tampil empty state (tidak ada baris)
    'emptyIcon' => 'inbox',
    'emptyTitle' => '',
    'emptyHint' => null,
])

<section {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                {{ $head }}
            </tr>
        </thead>
        @unless ($empty)
            <tbody class="divide-y divide-slate-100 text-sm">
                {{ $rows }}
            </tbody>
        @endunless
    </table>

    @if ($empty)
        <div class="py-12 flex flex-col items-center justify-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-3 border border-emerald-100">
                <x-dynamic-component :component="'lucide-'.$emptyIcon" class="w-7 h-7" />
            </div>
            <p class="text-base font-bold text-slate-800">{{ $emptyTitle }}</p>
            @if ($emptyHint)
                <p class="text-xs text-slate-500 mt-1 max-w-md">{{ $emptyHint }}</p>
            @endif
        </div>
    @endif
</section>
