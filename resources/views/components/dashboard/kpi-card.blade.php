@props([
    'icon' => 'activity',
    'tone' => 'navy',            // diabaikan: semua kartu seragam navy 3D
    'badge' => null,             // teks badge kanan-atas (opsional)
    'badgeTone' => 'slate',      // diabaikan: seragam
    'label' => '',
    'value' => '',
    'unit' => null,              // satuan kecil setelah angka (opsional)
    'hint' => null,
])

{{-- Kartu KPI: template 3D yang sama persis dengan tombol primary
     (card-shiny-emerald = navy + glass sheen + glow hover). --}}
<div {{ $attributes->merge(['class' => 'card-shiny-emerald rounded-[22px] p-6']) }}>
    <div class="flex items-center justify-between">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center border border-white/25 bg-white/15 text-white">
            <x-icon :name="'lucide-'.$icon" class="w-5 h-5" />
        </div>
        @if ($badge)
            <span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full border border-white/25 bg-white/15 text-white">
                {{ $badge }}
            </span>
        @endif
    </div>
    <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-sky-200">{{ $label }}</p>
    <p class="mt-1 text-3xl font-extrabold tracking-tight text-white">
        {{ $value }}
        @if ($unit)
            <span class="text-sm font-semibold text-sky-200">{{ $unit }}</span>
        @endif
    </p>
    @if ($hint)
        <p class="mt-2 text-xs text-sky-200/80">{{ $hint }}</p>
    @endif
</div>
