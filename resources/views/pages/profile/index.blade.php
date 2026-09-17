<x-app-layout>
    <x-slot:title>{{ __('general.profile.index._title') }}</x-slot:title>

    <main class="mb-auto px-8 pt-8 pb-12" x-data>
        <div class="flex gap-4 items-center">
            <a href="{{ route('admins.index') }}" class="clickable-ghost w-8 h-8 rounded-md">
                <x-lucide-chevron-left class="w-full h-full" />
            </a>
            <h1> {{ __('general.profile.index.menu') }} </h1>
        </div>

        <x-flash-alerts />

        <div class="content-card p-0">
            <form method="post" enctype="multipart/form-data" action="{{ $action }}">
                @csrf
                @method('put')

                <x-picture-upload :data="$data" type="update" />

                <div class="pb-8 px-8 flex flex-col">
                    <div class="input-container">
                        <div class="input-group">
                            <label for="name">{{ __('general.profile.form.labels.name') }}</label>
                            <input type="text" name="name" id="name"
                                placeholder="{{ __('general.profile.form.placeholders.name') }}"
                                value="{{ $data->name ?? '' }}" />
                            <small class="helper">{{ __('master.all.form.helpers.name') }}</small>
                            @error('name')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="nipp">{{ __('general.profile.form.labels.nipp') }}</label>
                            <input type="text" name="nipp" id="nipp"
                                placeholder="{{ __('general.profile.form.placeholders.nipp') }}."
                                value="{{ $data->nipp ?? '' }}" disabled />
                            <small class="helper">{{ __('master.all.form.helpers.nipp') }}</small>
                            @error('nipp')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="niptk">{{ __('general.profile.form.labels.niptk') }}</label>
                            <input type="text" name="niptk" id="niptk"
                                placeholder="{{ __('general.profile.form.placeholders.niptk') }}"
                                value="{{ $data->niptk ?? '' }}" disabled />
                            <small class="helper">{{ __('master.all.form.helpers.niptk') }}</small>
                            @error('niptk')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="border-b border-slate-700"></div>

                        <div class="input-group">
                            <label for="village">{{ __('general.profile.form.labels.address.village') }}</label>
                            <input type="text" name="village" id="village"
                                placeholder="{{ __('general.profile.form.placeholders.address.village') }}"
                                value="{{ $data->village ?? '' }}" />
                            <small class="helper">{{ __('master.all.form.helpers.address.village') }}</small>
                            @error('village')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="flex-1 input-group">
                                <label for="street">{{ __('general.profile.form.labels.address.street') }}</label>
                                <input type="text" name="street" id="street"
                                    placeholder="{{ __('general.profile.form.placeholders.address.street') }}"
                                    value="{{ $data->street ?? '' }}" />
                                <small class="helper">{{ __('master.all.form.helpers.address.street') }}</small>
                                @error('street')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <div class="input-group">
                                    <label for="zip_code">{{ __('general.profile.form.labels.address.zipcode') }}</label>
                                    <input type="text" name="zip_code" id="zip_code"
                                        placeholder="{{ __('general.profile.form.placeholders.address.zipcode') }}"
                                        value="{{ $data->zip_code ?? '' }}" />
                                    <small class="helper">{{ __('master.all.form.helpers.address.zipcode') }}</small>
                                    @error('zip_code')
                                        <small class="danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="input-group">
                                    <label
                                        for="tonarigumi">{{ __('general.profile.form.labels.address.tonarigumi') }}</label>
                                    <input type="text" name="tonarigumi" id="tonarigumi"
                                        placeholder="{{ __('general.profile.form.placeholders.address.tonarigumi') }}"
                                        value="{{ $data->tonarigumi ?? '' }}" />
                                    <small class="helper">{{ __('master.all.form.helpers.address.tonarigumi') }}</small>
                                    @error('tonarigumi')
                                        <small class="danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="district">{{ __('general.profile.form.labels.address.district') }}</label>
                            <input type="text" name="district" id="district"
                                placeholder="{{ __('general.profile.form.placeholders.address.district') }}"
                                value="{{ $data->district ?? '' }}" />
                            <small class="helper">{{ __('master.all.form.helpers.address.district') }}</small>
                            @error('district')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="regency">{{ __('general.profile.form.labels.address.city') }}</label>
                            <input type="text" name="regency" id="regency"
                                placeholder="{{ __('general.profile.form.placeholders.address.city') }}"
                                value="{{ $data->regency ?? '' }}" />
                            <small class="helper">{{ __('master.all.form.helpers.address.city') }}</small>
                            @error('regency')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="province">{{ __('general.profile.form.labels.address.state') }}</label>
                            <input type="text" name="province" id="province"
                                placeholder="{{ __('general.profile.form.placeholders.address.state') }}"
                                value="{{ $data->province ?? '' }}" />
                            <small class="helper">{{ __('master.all.form.helpers.address.province') }}</small>
                            @error('province')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>

                    <input type="submit"
                        value="{{ __('general.profile.form.buttons.update') }}"
                        class="clickable-primary py-2 rounded-md mt-4" />
                </div>
            </form>
        </div>
    </main>


</x-app-layout>
