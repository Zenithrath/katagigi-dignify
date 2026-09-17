<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<header class="flex items-center gap-3 bg-[#f8fafc] border-b border-slate-200/80 px-4 sm:px-6 py-2.5 sticky top-0 z-20">
    <button type="button" class="grid lg:hidden h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-500 hover:bg-slate-100" x-on:click="$dispatch('open-sidebar')" aria-label="Buka menu">
        <x-lucide-menu class="h-5 w-5" />
    </button>

    <div class="flex-1"></div>

    {{-- Profile dropdown --}}
    <div x-data="{ open: false }" class="relative">
        <button @click="open = ! open" class="flex items-center gap-2.5 rounded-xl bg-white border border-slate-200 py-1.5 pl-1.5 pr-3 hover:bg-slate-50 transition-colors shadow-sm">
            <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-600 text-xs font-bold text-white">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <span class="hidden md:block text-left leading-tight">
                <span class="block text-[13px] font-bold text-slate-900">{{ auth()->user()->name }}</span>
                <span class="block text-[11px] text-slate-500">{{ auth()->user()->email }}</span>
            </span>
            <x-lucide-chevron-down class="w-3.5 h-3.5 text-slate-400 hidden md:block transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" x-transition />
        </button>

        <div x-show="open" @click.away="open = false"
            class="absolute right-0 z-30 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            style="display: none;">

            {{-- User info --}}
            <div class="px-4 py-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-600 text-sm font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </div>

            {{-- Menu --}}
            <div class="py-1.5">
                <a href="{{ route('profile') }}" wire:navigate
                    class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                    <x-lucide-settings class="w-4 h-4 text-slate-400" />
                    Profile & Settings
                </a>
                <a href="{{ route('profile.change-password') }}" wire:navigate
                    class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                    <x-lucide-key-round class="w-4 h-4 text-slate-400" />
                    Change Password
                </a>
            </div>

            {{-- Logout --}}
            <div class="border-t border-slate-100 py-1.5">
                <button wire:click="logout"
                    class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    <x-lucide-log-out class="w-4 h-4" />
                    Log Out
                </button>
            </div>
        </div>
    </div>
</header>
