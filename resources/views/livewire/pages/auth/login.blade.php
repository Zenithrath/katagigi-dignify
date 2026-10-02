<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] #[Title('Masuk — KataGigi Dignify')] class extends Component {
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // Auto redirect ke dashboard (atau halaman tujuan sebelumnya) setelah login.
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-zinc-100 tracking-tight">Selamat datang kembali 👋</h1>
    <p class="text-sm text-slate-500 dark:text-zinc-400 mt-1 mb-6">Masuk untuk mengelola klinik Anda.</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-4">
        <div class="input-group">
            <label for="email">Email</label>
            <x-text-input wire:model="form.email" id="email" class="custom-input block w-full" type="email"
                name="email" required autofocus autocomplete="username" placeholder="nama@gmail.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <div class="relative mt-1" x-data="{ show: false }">
                <x-text-input wire:model="form.password" id="password" class="custom-input block w-full pr-10"
                    ::type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <button type="button"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none"
                    @click="show = !show">

                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>

                    <svg x-show="show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.025 10.025 0 014.132-5.4M9.88 9.88a3 3 0 114.24 4.24M10.804 10.804L14.75 14.75M21 21l-18-18m18 9c0 1.3-.36 2.52-.988 3.568m-1.742-1.742A8.966 8.966 0 0021 12c-1.274-4.057-5.064-7-9.542-7-1.393 0-2.713.284-3.918.798" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember"
                class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-zinc-300 cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" name="remember">
                Ingat saya
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-emerald-600 hover:text-emerald-700"
                    href="{{ route('password.request') }}" wire:navigate>
                    Lupa password?
                </a>
            @endif
        </div>

        <button type="submit" class="btn-submit w-full !py-2.5" wire:loading.attr="disabled"
            wire:loading.class="opacity-50">
            <span wire:loading.remove>Masuk</span>
            <span wire:loading>Memproses…</span>
        </button>
    </form>

    <p class="text-xs text-slate-400 dark:text-zinc-500 text-center mt-6">
        Registrasi mandiri dinonaktifkan. Akun dibuat oleh manajemen klinik.
    </p>
</div>
