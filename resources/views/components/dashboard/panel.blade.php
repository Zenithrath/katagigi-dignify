{{--
    Kartu panel dashboard: satu-satunya pola kartu (header icon + judul +
    subtitle + badge, body, footer opsional). Dipakai semua widget dashboard
    agar bentuk & jarak konsisten antar role.
--}}

@props([
    'icon' => 'activity',
    'title' => '',
    'subtitle' => null,
    'badge' => null,
    'badgeTone' => 'emerald',   // emerald | amber | slate
])

@php
    $badgeTones = [
        'emerald' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'amber' => 'bg-amber-50 text-amber-800 border-amber-200',
        'slate' => 'bg-slate-100 text-slate-600 border-slate-200',
    ];
    $badgeClass = $badgeTones[$badgeTone] ?? $badgeTones['emerald'];
@endphp

<section {{ $attributes->merge(['class' => 'rounded-[22px] bg-white p-6 sm:p-7 border border-slate-200/90 shadow-sm transition-all duration-300 hover:shadow-md']) }}>
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-5 mb-5">
        <div>
            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                <x-dynamic-component :component="'lucide-'.$icon" class="w-5 h-5 text-emerald-600" />
                {{ $title }}
            </h3>
            @if ($subtitle)
                <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if ($badge)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                {{ $badge }}
            </span>
        @endif
    </div>

    {{ $slot }}

    @isset($footer)
        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            {{ $footer }}
        </div>
    @endisset
</section>
