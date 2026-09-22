<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'KataGigi Dignify') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="shortcut icon" href="/favicon.svg" type="image/x-icon" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <script>
            (function () {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen grid lg:grid-cols-2">
            {{-- Panel kiri: branding (desktop saja) --}}
            <div class="hidden lg:flex flex-col justify-between bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-700 p-10 text-white relative overflow-hidden">
                <a href="/" wire:navigate class="flex items-center gap-3 relative z-10">
                    <img src="{{ asset('assets/logo.svg') }}" alt="KataGigi" class="h-10 w-10 rounded-xl bg-white/90 p-1" />
                    <span class="text-lg font-extrabold tracking-tight">{{ config('app.name', 'KataGigi') }}</span>
                </a>

                <div class="relative z-10 max-w-md">
                    <h1 class="text-3xl font-extrabold leading-tight mb-4">Rekam Medis Gigi Modern untuk Klinik Anda</h1>
                    <p class="text-emerald-50/90 leading-relaxed">
                        Odontogram digital, kode diagnosis ICD-10 & ICD-9-CM otomatis, tagihan, dan terhubung
                        SATUSEHAT — semua dalam satu aplikasi yang mudah dipakai.
                    </p>

                    <ul class="mt-8 space-y-3 text-sm text-emerald-50">
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            RME sesuai standar Kemenkes & SATUSEHAT
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Reminder pasien via WhatsApp Official
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Laporan keuangan & inventory real-time
                        </li>
                    </ul>
                </div>

                <p class="text-emerald-100/70 text-xs relative z-10">
                    © {{ date('Y') }} {{ config('app.name', 'KataGigi') }} · Sistem Informasi Manajemen Klinik Gigi
                </p>

                {{-- Dekorasi lingkaran lembut --}}
                <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-white/10"></div>
                <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-white/5"></div>
            </div>

            {{-- Panel kanan: form auth --}}
            <div class="relative flex flex-col justify-center items-center px-6 py-10 sm:px-12 bg-slate-50 dark:bg-zinc-950">
                {{-- Toggle tema (parity dengan topbar app) --}}
                <button type="button" x-data @click="
                        const root = document.documentElement;
                        const dark = root.classList.toggle('dark');
                        localStorage.setItem('theme', dark ? 'dark' : 'light');
                    "
                    class="dark-toggle absolute top-5 right-5 grid h-9 w-9 place-items-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-100 transition-colors"
                    title="Mode terang/gelap" aria-label="Ganti mode terang/gelap">
                    <x-lucide-moon class="h-4 w-4 dark:hidden" />
                    <x-lucide-sun class="h-4 w-4 hidden dark:block" />
                </button>
                <div class="w-full max-w-md">
                    {{-- Logo untuk mobile --}}
                    <a href="/" wire:navigate class="lg:hidden flex items-center justify-center gap-3 mb-8">
                        <img src="{{ asset('assets/logo.svg') }}" alt="KataGigi" class="h-10 w-10 rounded-xl bg-emerald-600 p-1" />
                        <span class="text-lg font-extrabold text-slate-900 dark:text-zinc-100">{{ config('app.name', 'KataGigi') }}</span>
                    </a>

                    {{ $slot }}
                </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
