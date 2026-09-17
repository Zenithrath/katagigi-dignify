{{-- Item menu sidebar gaya Donezo: kapsul hijau di tepi kiri + hover/active hijau muda.
     Animasi hanya saat hover (masuk halus); state aktif tampil instan. --}}
@props(['href', 'active' => false, 'icon' => 'house', 'badge' => null, 'collapsible' => false])

<li class="nav-item{{ $active ? ' active' : '' }}">
    <a href="{{ $href }}" {{ $attributes }}>
        <x-dynamic-component :component="'lucide-'.$icon" aria-hidden="true" />
        <span class="nav-label" @if($collapsible) x-data x-show="$store.sidenavExpanded.isExpanded" @endif>{{ $slot }}</span>
        @if ($badge)
            <span class="badge" @if($collapsible) x-data x-show="$store.sidenavExpanded.isExpanded" @endif>{{ $badge }}</span>
        @endif
    </a>
</li>
