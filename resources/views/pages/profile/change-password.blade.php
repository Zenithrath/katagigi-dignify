<x-app-layout>
    <x-slot:title>{{ __('general.password.index._title') }}</x-slot:title>

    <main class="mb-auto px-8 pt-8 pb-12" x-data>
        <div class="flex gap-4 items-center">
            <a href="{{ url()->previous() }}" class="clickable-ghost w-8 h-8 rounded-md">
                <x-lucide-chevron-left class="w-full h-full" />
            </a>
            <h1> {{ __('general.password.index._title') }} </h1>
        </div>

        <x-flash-alerts />

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
</x-app-layout>
