<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'KataGigi Dignify') }}</title>

        <!-- Fonts: Plus Jakarta Sans (gaya Donezo) -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="antialiased">
        <div class="min-h-screen bg-[#eef0f2] p-3 sm:p-4 flex gap-3 sm:gap-4 text-slate-900">

            {{-- Sidebar kartu putih --}}
            <aside class="hidden lg:flex w-60 shrink-0 flex-col rounded-3xl bg-white p-5 shadow-sm border border-white">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-1" wire:navigate>
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-emerald-700 text-white font-extrabold">K</span>
                    <span class="font-extrabold text-lg tracking-tight">KataGigi</span>
                </a>

                <p class="mt-7 mb-2 px-2 text-[11px] font-semibold uppercase tracking-widest text-slate-400">Menu</p>
                <nav class="space-y-1 text-[15px]">
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="relative flex items-center gap-3 rounded-xl px-3 py-2.5 font-semibold {{ request()->routeIs('dashboard') ? 'text-slate-900' : 'text-slate-500 hover:bg-slate-50' }}">
                        @if (request()->routeIs('dashboard'))
                            <span class="absolute -left-5 top-1.5 h-8 w-1.5 rounded-r-full bg-emerald-700"></span>
                        @endif
                        <span>⊞</span> Dashboard
                    </a>
                    @can('read schedule')
                        <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('schedules.*') ? 'font-semibold text-slate-900' : 'text-slate-500 hover:bg-slate-50' }}">◷ Jadwal</a>
                    @endcan
                    @can('read appointment')
                        <a href="{{ route('appointments.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('appointments.*') ? 'font-semibold text-slate-900' : 'text-slate-500 hover:bg-slate-50' }}">✚ Appointment</a>
                    @endcan
                    @can('read patient')
                        <a href="{{ route('patients.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('patients.*') ? 'font-semibold text-slate-900' : 'text-slate-500 hover:bg-slate-50' }}">♿ Pasien</a>
                    @endcan
                    @can('read medical record')
                        <a href="{{ route('medical-records.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('medical-records.*') ? 'font-semibold text-slate-900' : 'text-slate-500 hover:bg-slate-50' }}">☰ Rekam Medis</a>
                    @endcan
                    @can('read transaction')
                        <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('transactions.*') ? 'font-semibold text-slate-900' : 'text-slate-500 hover:bg-slate-50' }}">⇄ Transaksi</a>
                    @endcan
                    @can('read turnover')
                        <a href="{{ route('incomes.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('incomes.*') ? 'font-semibold text-slate-900' : 'text-slate-500 hover:bg-slate-50' }}">▤ Omzet</a>
                    @endcan
                    @role('manajemen')
                        <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-slate-500 hover:bg-slate-50">✓ Persetujuan <em class="ml-auto not-italic text-[10px] bg-slate-100 rounded-full px-2 py-0.5">via nota</em></a>
                    @endrole
                </nav>

                <p class="mt-6 mb-2 px-2 text-[11px] font-semibold uppercase tracking-widest text-slate-400">General</p>
                <nav class="space-y-1 text-[15px]">
                    <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('profile') ? 'font-semibold text-slate-900' : 'text-slate-500 hover:bg-slate-50' }}">⚙ Profile</a>
                    <livewire:sidebar-logout />
                </nav>

                <div class="mt-auto rounded-2xl bg-emerald-950 p-4 text-white">
                    <p class="font-semibold text-sm leading-snug">Panduan kasir & rekam medis</p>
                    <p class="mt-1 text-xs text-emerald-200/80">Alur baru: kode diagnosis wajib.</p>
                </div>
            </aside>

            {{-- Kolom utama --}}
            <div class="flex min-w-0 flex-1 flex-col gap-3 sm:gap-4">
                <livewire:layout.navigation />

                <main class="min-w-0 flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
