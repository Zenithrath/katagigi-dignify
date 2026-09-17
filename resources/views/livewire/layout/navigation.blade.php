<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<header class="flex items-center gap-3 bg-[#f8fafc] border-b border-slate-200/80 px-4 sm:px-6 py-3 sticky top-0 z-20">
    <button type="button" class="grid lg:hidden h-9 w-9 shrink-0 place-items-center rounded-md text-slate-500 hover:bg-slate-100" x-on:click="$dispatch('open-sidebar')" aria-label="Buka menu">
        <x-lucide-menu class="h-5 w-5" />
    </button>
    {{-- Search profesional --}}
    <div class="flex min-w-0 flex-1 items-center gap-2 rounded-md bg-slate-100 border border-slate-200 px-3 py-2 text-sm text-slate-500">
        <x-lucide-search class="h-4 w-4 shrink-0 text-slate-400" />
        <span class="truncate">Cari pasien / nota…</span>
        <kbd class="ml-auto hidden sm:inline rounded bg-white px-1.5 py-0.5 text-[11px] font-semibold text-slate-500 border border-slate-200">⌘K</kbd>
    </div>

    <button type="button" class="hidden sm:grid h-9 w-9 place-items-center rounded-md bg-slate-100 border border-slate-200 text-slate-500 hover:bg-slate-200" title="Pesan"><x-lucide-mail class="h-4 w-4" /></button>
    <button type="button" class="hidden sm:grid h-9 w-9 place-items-center rounded-md bg-slate-100 border border-slate-200 text-slate-500 hover:bg-slate-200" title="Notifikasi"><x-lucide-bell class="h-4 w-4" /></button>

    <div x-data="{ open: false }" class="relative hidden sm:block">
        <button @click="open = ! open" class="grid h-9 w-9 place-items-center rounded-md bg-slate-100 border border-slate-200 text-slate-500 hover:bg-slate-200" title="Bahasa" aria-label="Ganti bahasa">
            <x-lucide-languages class="h-4 w-4" />
        </button>
        <div x-show="open" @click.away="open = false" class="absolute right-0 z-30 mt-2 w-44 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg" style="display: none;">
            <a href="{{ route('switch-language', ['lang' => 'id']) }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">{{ __('Bahasa Indonesia') }}</a>
            <a href="{{ route('switch-language', ['lang' => 'en']) }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">{{ __('English') }}</a>
        </div>
    </div>

    <div x-data="{ open: false }" class="relative">
        <button @click="open = ! open" class="flex items-center gap-2.5 rounded-md bg-slate-100 border border-slate-200 py-1 pl-1 pr-3 hover:bg-slate-200">
            <span class="grid h-8 w-8 place-items-center rounded-full bg-emerald-700 text-sm font-bold text-white">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <span class="hidden md:block text-left leading-tight">
                <span class="block text-[13px] font-bold">{{ auth()->user()->name }}</span>
                <span class="block text-[11px] text-slate-500">{{ auth()->user()->email }}</span>
            </span>
        </button>
        <div x-show="open" @click.away="open = false" class="absolute right-0 z-30 mt-2 w-48 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg" style="display: none;">
            <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Profile</a>
            <button wire:click="logout" class="block w-full px-4 py-2 text-left text-sm text-slate-600 hover:bg-slate-50">Log Out</button>
        </div>
    </div>
</header>
