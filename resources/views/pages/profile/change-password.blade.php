<x-app-layout>
    <x-slot:title>{{ __('general.password.index._title') }}</x-slot:title>

    <main class="main-table-container" x-data>
        <section class="heading">
            <div>
                <h1>{{ __('general.password.index._title') }}</h1>
                <p>Update your account password</p>
            </div>
        </section>

        <x-flash-alerts />

        <div class="content-card p-0 overflow-hidden">
            <form method="post" action="{{ $action }}">
                @csrf
                @method('put')

                <div class="p-8 flex flex-col">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                        <div class="input-group">
                            <label for="password">{{ __('general.password.form.labels.password') }}</label>
                            <input type="password" name="password" id="password" class="custom-input"
                                placeholder="{{ __('general.password.form.placeholders.password') }}" />
                            <small class="helper">{{ __('master.all.form.helpers.password') }}</small>
                            @error('password')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="password_confirmation">{{ __('general.password.form.labels.confirm_password') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="custom-input"
                                placeholder="{{ __('general.password.form.placeholders.confirm_password') }}" />
                            <small class="helper">{{ __('master.all.form.helpers.password_confirmation') }}</small>
                            @error('password_confirmation')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-200 flex justify-end">
                        <button type="submit" class="btn-submit !w-auto !px-8">
                            {{ __('general.password.form.action.update') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</x-app-layout>
