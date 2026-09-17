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

<header class="flex items-center gap-3 rounded-3xl bg-white px-4 sm:px-5 py-3 shadow-sm border border-white">
    {{-- Search pill ala Donezo (⌘K) --}}
    <div class="flex min-w-0 flex-1 items-center gap-2 rounded-full bg-slate-100/80 px-4 py-2 text-sm text-slate-400">
        <span>⌕</span>
        <span class="truncate">Cari pasien / nota…</span>
        <kbd class="ml-auto hidden sm:inline rounded-md bg-white px-1.5 py-0.5 text-[11px] font-semibold text-slate-500 shadow-sm">⌘K</kbd>
    </div>

    <button type="button" class="hidden sm:grid h-10 w-10 place-items-center rounded-full bg-slate-100/80 text-slate-500" title="Pesan">✉</button>
    <button type="button" class="hidden sm:grid h-10 w-10 place-items-center rounded-full bg-slate-100/80 text-slate-500" title="Notifikasi">🔔</button>

    <div x-data="{ open: false }" class="relative">
        <button @click="open = ! open" class="flex items-center gap-2.5 rounded-full bg-slate-100/80 py-1 pl-1 pr-3">
            <span class="grid h-8 w-8 place-items-center rounded-full bg-emerald-700 text-sm font-bold text-white">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <span class="hidden md:block text-left leading-tight">
                <span class="block text-[13px] font-bold">{{ auth()->user()->name }}</span>
                <span class="block text-[11px] text-slate-500">{{ auth()->user()->email }}</span>
            </span>
        </button>
        <div x-show="open" @click.away="open = false" class="absolute right-0 z-30 mt-2 w-48 overflow-hidden rounded-2xl border border-slate-100 bg-white py-1 shadow-lg" style="display: none;">
            <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Profile</a>
            <button wire:click="logout" class="block w-full px-4 py-2 text-left text-sm text-slate-600 hover:bg-slate-50">Log Out</button>
        </div>
    </div>
</header>
