<button wire:click="logout" type="button">
    <x-lucide-log-out aria-hidden="true" />
    <span class="nav-label" @if($collapsible) x-data x-show="$store.sidenavExpanded.isExpanded" @endif>{{ __('navigation.topnav.logout') }}</span>
</button>
