{{-- Tombol kembali untuk sub-halaman (detail/show).
     Default: kembali ke halaman sebelumnya via history (fallback ke referrer
     bila history kosong, mis. halaman dibuka di tab baru). --}}
@props(['href' => null, 'label' => 'Kembali'])

@php
    $fallback = $href ?? (url()->previous() ?: route('dashboard'));
@endphp

<a href="{{ $fallback }}"
    x-data
    @click.prevent="if (document.referrer && window.history.length > 1) { window.history.back(); } else if (window.Livewire && typeof Livewire.navigate === 'function') { Livewire.navigate(@js($fallback)); } else { window.location = @js($fallback); }"
    wire:navigate
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-emerald-600 transition-colors']) }}>
    <x-icon name="lucide-arrow-left" class="w-4 h-4" aria-hidden="true" />
    {{ $label }}
</a>
