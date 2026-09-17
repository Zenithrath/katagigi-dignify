@extends('layouts.main-layout')

@section('_title', __('general.password.index._title'))
@section('header')
    <x-main-header title="{{ __('general.password.index.menu') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="mb-auto px-8 pt-8 pb-12" x-data>
        <div class="flex gap-4 items-center">
            <a href="{{ url()->previous() }}" class="clickable-ghost w-8 h-8 rounded-md">
                <x-icons.chevron-left />
            </a>
            <h1> {{ __('general.password.index._title') }} </h1>
        </div>

        @if (Session::has('success'))
            <div class="mb-8">
                <x-alerts.success message="{{ Session::get('success') }}" />
            </div>
        @endif

        @if (Session::has('error'))
            <div class="mb-8">
                <x-alerts.failed message="{{ Session::get('error') }}" />
            </div>
        @endif

        <div class="content-card p-0">
            <form method="post" action="{{ $action }}">
                @csrf
                @method('put')

                <div class="p-8 flex flex-col">
                    <div class="input-group mb-4">
                        <label for="password">{{ __('general.password.form.labels.password') }}</label>
                        <input type="password" name="password" id="password"
                            placeholder="{{ __('general.password.form.placeholders.password') }}" />
                        <small class="helper">{{ __('master.all.form.helpers.password') }}</small>
                        @error('password')
                            <small class="danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label
                            for="password_confirmation">{{ __('general.password.form.labels.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            placeholder="{{ __('general.password.form.placeholders.confirm_password') }}" />
                        <small class="helper">{{ __('master.all.form.helpers.password_confirmation') }}</small>
                        @error('password_confirmation')
                            <small class="danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <input type="submit"
                        value="{{ __('general.password.form.action.update') }}"
                        class="clickable-primary py-2 rounded-md mt-4" />
                </div>
            </form>
        </div>
    </main>
@endsection
