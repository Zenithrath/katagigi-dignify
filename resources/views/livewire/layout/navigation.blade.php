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

    {{-- Switcher bahasa (ID/EN) + mode terang/gelap --}}
    <div class="flex items-center gap-1.5">
        <div class="flex items-center rounded-xl bg-white border border-slate-200 overflow-hidden" role="group" aria-label="Bahasa">
            @foreach (['id' => 'ID', 'en' => 'EN'] as $code => $label)
                <a href="{{ route('switch-language', $code) }}"
                    class="px-2.5 py-2 text-xs font-bold transition-colors {{ app()->getLocale() === $code ? 'bg-emerald-600 text-white' : 'text-slate-500 hover:bg-slate-50' }}"
                    @if(app()->getLocale() === $code) aria-current="true" @endif>{{ $label }}</a>
            @endforeach
        </div>

        <button type="button" x-data @click="
                const root = document.documentElement;
                const dark = root.classList.toggle('dark');
                localStorage.setItem('theme', dark ? 'dark' : 'light');
            "
            class="dark-toggle grid h-9 w-9 place-items-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors"
            title="Mode terang/gelap" aria-label="Ganti mode terang/gelap">
            {{-- Ikon mengikuti state .dark pada <html> via CSS --}}
            <x-lucide-moon class="h-4 w-4 dark:hidden" />
            <x-lucide-sun class="h-4 w-4 hidden dark:block" />
        </button>
    </div>

    {{-- Fase 4: switcher cabang aktif --}}
    <form method="post" action="{{ route('branch.switch') }}" class="hidden md:block">
        @csrf
        <select name="branch_id" onchange="this.form.submit()" title="Cabang aktif"
            class="text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl px-2.5 py-2 hover:bg-slate-50 focus:outline-none focus:ring-emerald-500">
            <option value="">Semua cabang</option>
            @foreach (\Illuminate\Support\Facades\DB::table('branches')->where('is_active', true)->orderBy('code')->get() as $branch)
                <option value="{{ $branch->id }}" @selected(session('branch_id') === $branch->id)>{{ $branch->code }} — {{ $branch->name }}</option>
            @endforeach
        </select>
    </form>

    {{-- Profile dropdown --}}
    <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
        <button @click="open = ! open" type="button" class="flex items-center gap-2.5 rounded-xl bg-white border border-slate-200 py-1.5 pl-1.5 pr-3 hover:bg-slate-50 transition-colors shadow-sm">
            <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-600 text-xs font-bold text-white">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <span class="hidden md:block text-left leading-tight">
                <span class="block text-[13px] font-bold text-slate-900">{{ auth()->user()->name }}</span>
                <span class="block text-[11px] text-slate-500">{{ auth()->user()->email }}</span>
            </span>
            <x-lucide-chevron-down class="w-3.5 h-3.5 text-slate-400 hidden md:block transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
        </button>

        <div x-show="open" x-cloak
            class="absolute right-0 z-30 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
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
                    class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                    <x-lucide-settings class="w-4 h-4 text-slate-400" />
                    Profile & Password
                </a>
            </div>

            {{-- Logout: form POST native (tanpa JS), paling tahan banting --}}
            <div class="border-t border-slate-100 py-1.5">
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <x-lucide-log-out class="w-4 h-4" />
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
