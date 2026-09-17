<header id="main-header" class="header-container">
    <nav class="header">
        <div class="flex items-center justify-center gap-4">
            <button class="clickable-ghost icon-only" id="sidenav-toggler" x-data
                @click="$store.sidenavExpanded.setIsExpanded()">
                <template x-if="$store.sidenavExpanded.isExpanded">
                    <x-icons.playlist-x />
                </template>
                <template x-if="!$store.sidenavExpanded.isExpanded">
                    <x-icons.menu2 />
                </template>
            </button>
            <div class="text-lg font-semibold">{{ $attributes['title'] ?? 'Dashboard' }}</div>
        </div>

        <div class="flex items-center gap-4">
            <div class="profile-bar-container" x-data="{ isOpened: false }">
                <button @click="isOpened = !isOpened" class="clickable-ghost icon-only !p-0">
                    <x-icons.language-hiragana />
                </button>

                <div x-show="isOpened" class="dropdown-menu" role="menu" aria-orientation="vertical"
                    aria-labelledby="menu-button" tabindex="-1" x-transition.duration-300>
                    <div class="item-container" role="none">
                        <a href="{{ route('switch-language', ['lang' => 'id']) }}" role="menuitem" tabindex="-1"
                            id="menu-item-0">{{ __('Bahasa Indonesia') }}</a>
                        <a href="{{ route('switch-language', ['lang' => 'en']) }}" role="menuitem" tabindex="-1"
                            id="menu-item-0">{{ __('English') }}</a>
                    </div>
                </div>
            </div>

            <div class="profile-bar-container" x-data="{ isOpened: false }">
                <button @click="isOpened = !isOpened" class="clickable-ghost icon-only !p-0">
                    <div class="default-profile">
                        @if (Session::has('profile-picture') && Session::get('profile-picture'))
                            <img src="{{ Session::get('profile-picture') }}" class="rounded-full"
                                alt="{{ Auth::user()->name }}'s Profile Picture">
                        @else
                            <x-icons.user-circle />
                        @endif
                    </div>
                </button>

                <div x-show="isOpened" class="dropdown-menu" role="menu" aria-orientation="vertical"
                    aria-labelledby="menu-button" tabindex="-1" x-transition.duration-300>
                    <div class="item-container" role="none">
                        <a href="{{ route('profile') }}" role="menuitem" tabindex="-1"
                            id="menu-item-0">{{ __('navigation.topnav.account_settings') }}</a>
                        <a href="{{ route('profile.change-password') }}" role="menuitem" tabindex="-1"
                            id="menu-item-0">{{ __('Password Settings') }}</a>
                        <a href="{{ route('logout') }}" role="menuitem" tabindex="-1"
                            id="menu-item-2">{{ __('navigation.topnav.logout') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

@pushOnce('scripts')
    <script lang="text/javascript">
        window.addEventListener("scroll", function() {
            var header = document.querySelector("#main-header");
            header.classList.toggle("header-glassmorphism", window.scrollY > 0);
        });
    </script>
@endPushOnce
