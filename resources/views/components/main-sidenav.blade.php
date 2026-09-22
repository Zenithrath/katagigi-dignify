@props(['items' => []])

<aside {{ $attributes->merge(['class' => 'main-sidenav']) }}>
    <nav class="flex flex-col gap-1">
        @foreach ($items as $item)
            @if(($item['show'] ?? true) && (!isset($item['permission']) || auth()->user()?->can($item['permission'])))
                <a href="{{ $item['url'] }}"
                   @class([
                       'sidebar-link',
                       'active' => request()->routeIs($item['active'] ?? ''),
                   ])>
                    @isset($item['icon'])
                        <span class="icon" aria-hidden="true">{!! $item['icon'] !!}</span>
                    @endisset
                    <span>{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>
    <div class="mt-auto border-t border-slate-200 pt-3 dark:border-slate-700">
        <livewire:sidebar-logout />
    </div>
</aside>
