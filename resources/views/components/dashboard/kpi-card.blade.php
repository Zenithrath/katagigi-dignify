@props([
    'icon' => 'activity',
    'tone' => 'emerald',          // emerald | blue | amber | teal | rose
    'badge' => null,              // teks badge kanan-atas (opsional)
    'badgeTone' => 'slate',       // slate | emerald
    'label' => '',
    'value' => '',
    'unit' => null,               // satuan kecil setelah angka (opsional)
    'hint' => null,
])

@php
    $tones = [
        'emerald' => 'bg-emerald-50 border-emerald-100 text-emerald-700',
        'blue' => 'bg-blue-50 border-blue-100 text-blue-600',
        'amber' => 'bg-amber-50 border-amber-100 text-amber-600',
        'teal' => 'bg-teal-50 border-teal-100 text-teal-600',
        'rose' => 'bg-rose-50 border-rose-100 text-rose-600',
    ];
    $toneClass = $tones[$tone] ?? $tones['emerald'];
    $badgeClass = $badgeTone === 'emerald'
        ? 'text-emerald-700 bg-emerald-50 border-emerald-200/70'
        : 'text-slate-600 bg-slate-100 border-slate-200';
@endphp

<div {{ $attributes->merge(['class' => 'rounded-[22px] bg-white p-6 border border-slate-200/90 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-lg hover:border-slate-300 relative overflow-hidden cursor-default']) }}>
    <div class="flex items-center justify-between">
        <div class="w-10 h-10 rounded-xl {{ $toneClass }} border flex items-center justify-center">
            <x-dynamic-component :component="'lucide-'.$icon" class="w-5 h-5" />
        </div>
        @if ($badge)
            <span class="inline-flex items-center text-[11px] font-semibold {{ $badgeClass }} px-2.5 py-0.5 rounded-full border">
                {{ $badge }}
            </span>
        @endif
    </div>
    <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</p>
    <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">
        {{ $value }}
        @if ($unit)
            <span class="text-sm font-semibold text-slate-400">{{ $unit }}</span>
        @endif
    </p>
    @if ($hint)
        <p class="mt-2 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
