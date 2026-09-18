<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'KataGigi Dignify') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet" />
        <link rel="shortcut icon" href="/favicon.svg" type="image/x-icon" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="antialiased">
        <div class="app-shell" x-data="{ sidebarOpen: false, sidebarCollapsed: false }" x-on:open-sidebar.window="sidebarOpen = true">

            {{-- Sidebar: full-height, flush edges --}}
            <aside class="app-sidebar hidden lg:flex flex-col sticky top-0 h-screen bg-[#f8fafc] border-r border-slate-200/80"
                :class="sidebarCollapsed ? 'collapsed' : ''">
                <a href="{{ route('dashboard') }}" class="logo" wire:navigate>
                    <img src="{{ asset('assets/logo.svg') }}" alt="KataGigi" />
                </a>
                <div class="sidebar-scroll min-h-0 flex-1 overflow-y-auto">
                    <x-sidebar-nav />
                </div>
                {{-- Collapse button --}}
                <div class="p-3 border-t border-slate-200/80">
                    <button type="button" @click="sidebarCollapsed = !sidebarCollapsed"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors text-xs font-medium">
                        <x-lucide-panel-left-close class="w-4 h-4" x-show="!sidebarCollapsed" />
                        <x-lucide-panel-left-open class="w-4 h-4" x-show="sidebarCollapsed" />
                        <span x-show="!sidebarCollapsed" x-transition>Collapse</span>
                    </button>
                </div>
            </aside>

            {{-- Drawer mobile --}}
            <div x-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden" style="display: none;">
                <div x-show="sidebarOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/50" x-on:click="sidebarOpen = false"></div>
                <aside class="app-sidebar absolute inset-y-0 left-0 flex h-full w-64 flex-col bg-[#f8fafc] border-r border-slate-200/80 shadow-xl z-50" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                    <div class="relative flex items-center justify-center px-4 pt-4 pb-4">
                        <a href="{{ route('dashboard') }}" class="logo !mb-0 !p-0" wire:navigate>
                            <img src="{{ asset('assets/logo.svg') }}" alt="KataGigi" />
                        </a>
                        <button type="button" x-on:click="sidebarOpen = false" class="absolute right-3 grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-200/60" aria-label="Tutup menu">
                            <x-lucide-x class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="sidebar-scroll min-h-0 flex-1 overflow-y-auto" x-on:click="if ($event.target.closest('a')) sidebarOpen = false">
                        <x-sidebar-nav />
                    </div>
                </aside>
            </div>

            {{-- Main column --}}
            <div class="main-column flex min-w-0 flex-1 flex-col min-h-screen">
                {{-- Topbar --}}
                <livewire:layout.navigation />

                {{-- Content area: white background --}}
                <main class="content-area flex-1">
                    @isset($header)
                        <div class="border-b border-slate-100 px-4 sm:px-6 py-4">{{ $header }}</div>
                    @endisset

                    <div class="p-4 sm:p-6">
                        {{ $slot }}
                    </div>
                </main>

                <x-main-footer />
            </div>
        </div>

        @isset($printable)
            <div class="print-base">{{ $printable }}</div>
        @endisset

        @stack('scripts')
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', () => {
                const elems = document.getElementsByClassName('selectable');
                for (let index = 0; index < elems.length; index++) {
                    try { initSelectable(elems[index]); } catch (_) {}
                }
            });
            function initSelectable(element) {
                if (!window.$ || !window.$.fn || typeof window.$.fn.select2 !== 'function') return;
                $(element).select2({
                    width: '100%',
                    id: element.getAttribute('id'),
                    dropdownParent: $(element).parent()
                });
            }
        </script>
        @livewireScripts
    </body>
</html>
