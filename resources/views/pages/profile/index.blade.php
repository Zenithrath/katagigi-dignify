<x-app-layout>
    <x-slot:title>{{ __('general.profile.index._title') }}</x-slot:title>

    <main class="main-table-container" x-data>
        <section class="heading">
            <div>
                <h1>{{ __('general.profile.index.menu') }}</h1>
                <p>{{ __('general.profile.index._title') }}</p>
            </div>
        </section>

        <x-flash-alerts />

        <div class="content-card p-0 overflow-hidden">
            <form method="post" enctype="multipart/form-data" action="{{ $action }}">
                @csrf
                @method('put')

                <x-picture-upload :data="$data" type="update" />

                <div class="p-8 flex flex-col">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                        <div class="input-group">
                            <label for="name">{{ __('general.profile.form.labels.name') }}</label>
                            <input type="text" name="name" id="name" class="custom-input"
                                placeholder="{{ __('general.profile.form.placeholders.name') }}"
                                value="{{ $data->name ?? '' }}" />
                            <small class="helper">{{ __('master.all.form.helpers.name') }}</small>
                            @error('name')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="nipp">{{ __('general.profile.form.labels.nipp') }}</label>
                            <input type="text" name="nipp" id="nipp" class="custom-input"
                                placeholder="{{ __('general.profile.form.placeholders.nipp') }}"
                                value="{{ $data->nipp ?? '' }}" disabled />
                            <small class="helper">{{ __('master.all.form.helpers.nipp') }}</small>
                        </div>

                        <div class="input-group">
                            <label for="niptk">{{ __('general.profile.form.labels.niptk') }}</label>
                            <input type="text" name="niptk" id="niptk" class="custom-input"
                                placeholder="{{ __('general.profile.form.placeholders.niptk') }}"
                                value="{{ $data->niptk ?? '' }}" disabled />
                            <small class="helper">{{ __('master.all.form.helpers.niptk') }}</small>
                        </div>

                        <div class="input-group">
                            <label for="village">{{ __('general.profile.form.labels.address.village') }}</label>
                            <input type="text" name="village" id="village" class="custom-input"
                                placeholder="{{ __('general.profile.form.placeholders.address.village') }}"
                                value="{{ $data->village ?? '' }}" />
                            @error('village')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="street">{{ __('general.profile.form.labels.address.street') }}</label>
                            <input type="text" name="street" id="street" class="custom-input"
                                placeholder="{{ __('general.profile.form.placeholders.address.street') }}"
                                value="{{ $data->street ?? '' }}" />
                            @error('street')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="zip_code">{{ __('general.profile.form.labels.address.zipcode') }}</label>
                            <input type="text" name="zip_code" id="zip_code" class="custom-input"
                                placeholder="{{ __('general.profile.form.placeholders.address.zipcode') }}"
                                value="{{ $data->zip_code ?? '' }}" />
                            @error('zip_code')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="tonarigumi">{{ __('general.profile.form.labels.address.tonarigumi') }}</label>
                            <input type="text" name="tonarigumi" id="tonarigumi" class="custom-input"
                                placeholder="{{ __('general.profile.form.placeholders.address.tonarigumi') }}"
                                value="{{ $data->tonarigumi ?? '' }}" />
                            @error('tonarigumi')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="district">{{ __('general.profile.form.labels.address.district') }}</label>
                            <input type="text" name="district" id="district" class="custom-input"
                                placeholder="{{ __('general.profile.form.placeholders.address.district') }}"
                                value="{{ $data->district ?? '' }}" />
                            @error('district')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="regency">{{ __('general.profile.form.labels.address.city') }}</label>
                            <input type="text" name="regency" id="regency" class="custom-input"
                                placeholder="{{ __('general.profile.form.placeholders.address.city') }}"
                                value="{{ $data->regency ?? '' }}" />
                            @error('regency')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="province">{{ __('general.profile.form.labels.address.state') }}</label>
                            <input type="text" name="province" id="province" class="custom-input"
                                placeholder="{{ __('general.profile.form.placeholders.address.state') }}"
                                value="{{ $data->province ?? '' }}" />
                            @error('province')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-200 flex justify-end">
                        <button type="submit" class="btn-submit !w-auto !px-8">
                            {{ __('general.profile.form.buttons.update') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Ganti password — digabung di halaman yang sama --}}
        <div class="content-card p-0 overflow-hidden">
            <form method="post" action="{{ route('profile.change-password.update', auth()->user()->id) }}">
                @csrf
                @method('put')

                <div class="p-8 flex flex-col">
                    <div class="card-header-clean !mb-4">
                        <div class="icon-box">
                            <x-lucide-key-round class="w-5 h-5" />
                        </div>
                        <div>
                            <h2>{{ __('general.password.form.labels.password') }}</h2>
                            <p class="text-xs text-slate-400">Update your account password</p>
                        </div>
                    </div>

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
