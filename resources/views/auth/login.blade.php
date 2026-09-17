@extends('layouts.auth-layout')

@section('_title', 'Sign In')

@section('content')
    <div class="auth-container">
        <div class="auth-title-container">
            <img src="{{ asset('assets/logo.svg') }}" alt="App Logo" class="auth-institution-logo" />
            <h1>{{ __('authentication.login.title') }}</h1>
        </div>

        <div class="auth-form-container" x-data>
            <form action="{{ route('login') }}" method="post">
                <div class="input-container">
                    @csrf

                    <div class="input-group">
                        <label for="email">{{ __('authentication.login.email.title') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            placeholder="{{ __('authentication.login.email.placeholder') }}" />
                        <small class="helper">{{ __('form.helpers.incase-sensitive') }}</small>
                        @error('email')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="password">{{ __('authentication.login.password.title') }}</label>

                        <div class="password-input-container" x-data="{ isRevealed: false }">
                            <input :type="isRevealed ? 'text' : 'password'" name="password" id="password"
                                value="{{ old('password') }}" required
                                placeholder="{{ __('authentication.login.password.placeholder') }}" class="w-full" />
                            <button type="button" class="auth-password-revealer"
                                @click.prevent="isRevealed = !isRevealed">Show</button>
                        </div>
                        @error('password')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="block">
                        <label for="remember_me" class="flex items-center gap-2">
                            <input id="remember_me" type="checkbox" class="auth-checkbox" name="remember">
                            <span class="auth-checkbox-caption">{{ __('authentication.login.remember') }}</span>
                        </label>
                    </div>
                </div>

                <input type="submit" name="submit" id="submit" value="{{ __('authentication.login.submit') }}"
                    class="clickable-primary py-2 rounded-md" />

            </form>
        </div>
    </div>
@endsection

@pushOnce('scripts')
    <script>
        // function for reveal password with alpinejs
        function revealPassword(event) {
            event.preventDefault();
            isRevealed = !isRevealed;
        }
    </script>
@endPushOnce
