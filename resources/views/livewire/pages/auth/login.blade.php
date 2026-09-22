<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] #[Title('Masuk — KataGigi Dignify')]
class extends Component
{
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
            <x-text-input wire:model="form.email" id="email" class="custom-input block w-full" type="email" name="email" required autofocus autocomplete="username" placeholder="nama@klinik.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <x-text-input wire:model="form.password" id="password" class="custom-input block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-zinc-300 cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" name="remember">
                Ingat saya
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-emerald-600 hover:text-emerald-700" href="{{ route('password.request') }}" wire:navigate>
                    Lupa password?
                </a>
            @endif
        </div>

        <button type="submit" class="btn-submit w-full !py-2.5" wire:loading.attr="disabled" wire:loading.class="opacity-50">
            <span wire:loading.remove>Masuk</span>
            <span wire:loading>Memproses…</span>
        </button>
    </form>

    <p class="text-xs text-slate-400 dark:text-zinc-500 text-center mt-6">
        Registrasi mandiri dinonaktifkan. Akun dibuat oleh manajemen klinik.
    </p>
</div>
