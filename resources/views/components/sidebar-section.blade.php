{{-- Judul section sidebar gaya Donezo: kecil, caps, abu-abu. --}}
@props(['collapsible' => false])

<div class="menu-title" @if($collapsible) x-data :class="$store.sidenavExpanded.isExpanded ? null : 'hidden'" @endif {{ $attributes }}>{{ $slot }}</div>
